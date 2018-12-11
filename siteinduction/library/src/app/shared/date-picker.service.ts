import { Injectable } from '@angular/core';
import { FormData, DataMapper, Location, AppointmentValidation } from '../core';
import { IMyOptions } from 'mydatepicker';
import * as _ from 'lodash';

@Injectable({
  providedIn: 'root'
})
export class DatePickerService {

  public datePickerOptions: IMyOptions;
  private _fSContractor: boolean = false;
  private _isWhitelisted: boolean = false;
  private _formData: FormData;
  private today: Date;
  private nextYear: Date;
  private weekdays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];

  constructor(private mapper: DataMapper) {
    this.today = new Date();
    this.nextYear = new Date(this.today.getFullYear() + 1, this.today.getMonth() + 1, 0);
    this.datePickerOptions = {
      todayBtnTxt: 'Today',
      dateFormat: 'dd-mmm-yyyy',
      firstDayOfWeek: 'mo',
      sunHighlight: true,
      height: '36px',
      inline: false,
      disableDays: [],
      disableUntil: this.mapper.mapDate(this.today),
      disableWeekends: true,
      selectionTxtFontSize: '13px',
      componentDisabled: true,
      editableDateField: false,
      alignSelectorRight: true,
      showClearDateBtn: false,
      showTodayBtn: false,
      openSelectorOnInputClick: true
    };

  }

  // Get method for Facility Servicing Contractor
  get fSContractor(): boolean {
    return this._fSContractor;
  }

  // Get method for Form Data
  get formData(): FormData {
    return this._formData;
  }

  // Set method for Form Data
  set formData(formData: FormData) {
    this._formData = formData;
    this._fSContractor = this.formData.facilityServicingContractor === 'Yes';
    this._isWhitelisted = this.formData.isWhitelisted;
  }

  // Map Location details and data
  public mapLocations(): Location[] {
    let loc: Location[] = [];
    for (let location of this.formData.locations) {
      let datepickerOption: IMyOptions = this.getDatePickerOptions();
      let inspectDays = this.mapper.mapInspectionDays(location, this.fSContractor, this._isWhitelisted);
      datepickerOption.disableDays = this.getDisabledDates(inspectDays);
      datepickerOption.componentDisabled = false;

      loc.push(
        new Location(
          location.post_title,
          location.location,
          location.post_name,
          location.sub_content,
          location.disclaimer,
          location.seat_capacity,
          location.info,
          false,
          '',
          null,
          datepickerOption,
          this.mapper.mapInspectionTime(location, this.fSContractor, this._isWhitelisted),
          {},
          new AppointmentValidation('', false, '')
        )
      );
    }
    return loc;
  }

  // Clone, Stringify and parse the Date Picker Options
  public getDatePickerOptions() {
    return JSON.parse(JSON.stringify(this.datePickerOptions));
  }

  // Search/disables all dates based on a specific date range, holidays and permitted days
  public getDisabledDates(permittedDays: any[]): any[] {
    let days = [];
    let currentDate = new Date(this.today);
    while (currentDate <= this.nextYear) {
      if (_.find(this._formData.holidays, this.mapper.mapDate(currentDate)) ||
        _.indexOf(permittedDays, this.weekdays[currentDate.getDay() - 1]) < 0) {
        days.push(this.mapper.mapDate(currentDate));
      }
      currentDate.setDate(currentDate.getDate() + 1);
    }
    return days;
  }
}
