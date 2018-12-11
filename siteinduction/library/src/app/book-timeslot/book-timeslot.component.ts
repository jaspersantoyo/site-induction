import {
  Component,
  OnInit
} from '@angular/core';
import { WizardService } from '../shared/wizard.service';
import {
  DciAppSteps,
  EntryFormFields,
  LocalStorageKeys,
  Location
} from '../core';
import { LocalStorageService } from '../commons';
import { DatePickerService } from '../shared/date-picker.service';
import { WpAdminAjaxService } from '../shared/wp-admin-ajax.service';
import * as moment from 'moment-timezone';

@Component({
  selector: 'book-timeslot',
  templateUrl: './book-timeslot.component.html',
  styleUrls: ['./book-timeslot.component.scss']
})
export class BookTimeslotComponent implements OnInit {

  public hasSelectedLocation = false;
  public data: EntryFormFields;
  public areDateValid = false;
  public isSubmited = false;
  public isDataLoaded = false;

  constructor(
    private wizardService: WizardService,
    private localStorage: LocalStorageService,
    private datepickerService: DatePickerService,
    private wpAdminAjaxService: WpAdminAjaxService) {
  }

  public ngOnInit() {
    this.data = this.localStorage.get(LocalStorageKeys.USER_FORM) as EntryFormFields;
    this.data.locations = this.data ? this.data.locations :
      this.datepickerService.mapLocations();

    let targetCompany = this.data.contractedCompany === '' || !this.data.contractedCompany ?
      this.data.company : this.data.contractedCompany;
    let currentTargetDate: string;

    // Get all disabled timeslots that are already selected by other users
    for (let loc of this.data.locations) {
      let timeSlots: any[] = [];
      if (loc.selected) {
        // Ajax call on db via wp-admin
        this.wpAdminAjaxService.sendRequest('get_disabled_dates', { 'location': loc.title })
          .subscribe((results) => {
            for (let result of results) {

              let idate = moment.tz(moment.utc(result.appointment_date), 'Australia/Sydney').format('YYYY-MM-DD HH:mm');
              let dDate = idate.split(' ')[0];
              let dTime = idate.split(' ')[1];

              if (currentTargetDate !== dDate) {
                timeSlots = loc.scheduleTime.slice(0);
                currentTargetDate = dDate;
              }

              // disable data or time
              let companyIsDifferent = result.company !== targetCompany.toLowerCase().trim();
              let timeslotIsFull = result.count >= loc.seatCapacity;

              if (companyIsDifferent || timeslotIsFull) {
                // remove time if diff
                let iTimeSlot = timeSlots.indexOf(dTime);
                if (iTimeSlot > -1) {
                  timeSlots.splice(iTimeSlot, 1);
                }
                // update timeslots for the selected date
                loc.disabledDates[currentTargetDate] = timeSlots;
                // check if timeslot has still items, disable the whole date if the timeslot are all taken
                if (timeSlots.length === 0) {
                  loc.datePickerOptions.disableDays.push(
                    { 'day': +dDate.split('-')[2], 'month': +dDate.split('-')[1], 'year': +dDate.split('-')[0] }
                  );
                }
                continue;
              }
            }
            this.localStorage.set(LocalStorageKeys.USER_FORM, this.data);
            this.isDataLoaded = true;
          });
      }
    }
  }

  // Navigate to the next Page/Step
  public next(event: any): void {
    event.preventDefault();

    let appointments = { appointments: [] };

    // Create appointment objects to validate
    for (let loc of this.data.locations) {
      if (loc.selected) {
        let sCompany = this.data.contractedCompany ? this.data.contractedCompany : this.data.company;
        let appointmnetDate = moment
          .tz(loc.datepicker.formatted + ' ' + loc.time, 'DD-MMM-YYYY HH:mm', 'Australia/Sydney')
          .utc()
          .format('YYYY-MM-DD HH:mm');

        let appointment = {
          location: loc.title,
          appointmentDate: appointmnetDate,
          company: sCompany,
          seatCapacity: loc.seatCapacity
        };
        appointments.appointments.push(appointment);
      }
    }

    this.wpAdminAjaxService.sendRequest('validate_date_not_full', appointments)
      .subscribe((res) => {

        let areAllValid = true;
        // Validates all the appointments via ajax call
        for (let r of res) {
          for (let loc of this.data.locations) {
            if (loc.title === r.location) {
              loc.validation = r;
              loc.validation.appointment
                = moment.tz(loc.validation.appointment, 'Australia/Sydney').format('DD-MMM-YYYY HH:mm');
            }
          }
          if (!r.isValid) {
            areAllValid = false;
          }
        }

        this.isSubmited = true;

        if (areAllValid) {
          this.localStorage.set(LocalStorageKeys.USER_FORM, this.data);
          this.wizardService.nextStep(DciAppSteps.SUMMARY);
          this.areDateValid = true;
        }
      });
  }

  // Disable Timeslots per date selected
  public onDateChanged(event: any, location: Location) {
    let date = {
      year: event.date.year,
      month: event.date.month - 1,
      day: event.date.day
    };

    let selectedDate = moment(date).format('YYYY-MM-DD');

    // Check if its in the list of disabled dates, assign appropriate schedule time if true
    for (let key in location.disabledDates) {
      if (key === selectedDate) {
        location.scheduleTime = location.disabledDates[key];
        break;
      }
    }
    location.time = '';
  }

  // Returns true if location passed has value, false otherwise
  public disableDropdown(loc: Location): any {
    if (loc.datepicker) {
      return null;
    }
    return '';
  }
}
