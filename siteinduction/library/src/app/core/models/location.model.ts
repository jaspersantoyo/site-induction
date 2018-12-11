import { IMyDateModel, IMyOptions } from 'mydatepicker';
import { AppointmentValidation } from './appointment-validation.model';

export class Location {

  constructor(
    public title: string,
    public location: string,
    public slug: string,
    public subcontent: string,
    public disclaimer: string,
    public seatCapacity: number,
    public info: string,
    public selected: boolean,
    public time: string,
    public datepicker: IMyDateModel,
    public datePickerOptions: IMyOptions,
    public scheduleTime: string[],
    public disabledDates: any,
    public validation: AppointmentValidation
  ) {
  }
}
