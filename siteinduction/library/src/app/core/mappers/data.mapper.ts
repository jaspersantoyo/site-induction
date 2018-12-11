import { Injectable } from '@angular/core';
import { FormData, DownloadPrint } from '../models';

@Injectable()
export class DataMapper {

  public mapFormData(data: any): FormData {
    return new FormData(
      data.intellicentres,
      data.intellicentreSettings.general.inspection_days,
      data.intellicentreSettings.general.inspection_hours,
      data.intellicentreSettings.general.holidays,
      data.intellicentreSettings.general.facility_servicing_contractor,
      data.intellicentreSettings.general.is_whitelisted
    );
  }

  // Map Download and Print model
  public mapDownloadAndPrintData(downloadPrint: any) {
    return new DownloadPrint(
      downloadPrint.heading,
      downloadPrint.content,
      downloadPrint.button_label,
      downloadPrint.form_link);
  }

  // Map inspection days
  public mapInspectionDays(location: any, fSContractor: boolean, isWhitelisted: boolean) {
    if (fSContractor) {
      return location.data_centre_induction_schedule.facility_servicing_contractor.day;
    }

    return isWhitelisted ? location.data_centre_induction_schedule.macquarie_staff.day
      : location.data_centre_induction_schedule.customer.day;
  }

  // Map inspection time
  public mapInspectionTime(location: any, fSContractor: boolean, isWhitelisted: boolean) {

    if (fSContractor) {
      return location.data_centre_induction_schedule.facility_servicing_contractor.time;
    }

    return isWhitelisted ? location.data_centre_induction_schedule.macquarie_staff.time
      : location.data_centre_induction_schedule.customer.time;
  }

  // Returns an object representation of a date
  public mapDate(date: Date) {
    return { 'day': date.getDate(), 'month': date.getMonth() + 1, 'year': date.getFullYear() };
  }
}
