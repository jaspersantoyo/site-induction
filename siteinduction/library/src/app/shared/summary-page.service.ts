import { Injectable } from '@angular/core';
import {
  EntryFormFields,
  Summary,
  Appointment,
  Location,
  Quiz
} from '../core';
import * as moment from 'moment-timezone';

@Injectable()
export class SummaryPageService {

  public mapAppointment(entryFormData: EntryFormFields): Appointment[] {
    let appointment: Appointment[] = [];
    let locations: Location[] = entryFormData.locations;

    for (let location of locations) {
      if (location.selected) {
        appointment.push(
          new Appointment(
            location.title,
            location.subcontent,
            location.time,
            location.datepicker.date,
            location.datepicker.jsdate,
            moment.tz(location.datepicker.formatted + ' ' + location.time, 'DD-MMM-YYYY HH:mm', 'Australia/Sydney')
            .utc()
            .format()
          )
        );
      }
    }
    return appointment;
  }

  public mapSummaryData(summaryData: any, appointment: Appointment[]): Summary {
    return new Summary(
      summaryData.heading,
      summaryData.success_message,
      summaryData.finish_button_link,
      summaryData.uuid,
      appointment
    );
  }

  public mapUserData(entryFormData: EntryFormFields, quizData: Quiz[], summary: Summary): any {
    return {
      // TODO: refactor into a model, for type safe
      'customer': {
        'fullname': entryFormData.fullname,
        'firstname': entryFormData.firstname,
        'lastname': entryFormData.lastname,
        'email': entryFormData.email,
        'company': entryFormData.company,
        'contractedCompany': entryFormData.contractedCompany,
        'uuid': summary.uuid
      },
      'quizData': quizData,
      'summary': summary
    };
  }
}
