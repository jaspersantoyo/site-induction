import { IMyDateModel, IMyOptions } from 'mydatepicker';

export class Location {

  constructor(
    public title: string,
    public location: string,
    public slug: string,
    public subcontent: string,
    public disclaimer: string,
    public selected: boolean,
    public time: string,
    public datepicker: IMyDateModel,
    public datePickerOptions: IMyOptions,
    public scheduleTime: string[]
  ) { }
}
