import { Injectable } from '@angular/core';

@Injectable()
export class DateUtil {

  public static convertTo24Hr(timeIn12Hr: string): string {
    let hours: number = +timeIn12Hr.match(/^(\d+)/)[1];
    let minutes: number = +timeIn12Hr.match(/:(\d+)/)[1];
    let AMPM = timeIn12Hr.match(/\s(.*)$/)[1];
    if (AMPM === 'PM' && hours < 12) { hours = hours + 12; }
    if (AMPM === 'AM' && hours === 12) { hours = hours - 12; }
    let sHours = hours.toString();
    let sMinutes = minutes.toString();
    if (hours < 10) { sHours = '0' + sHours; }
    if (minutes < 10) { sMinutes = '0' + sMinutes; }
    return sHours + ':' + sMinutes;
  }

  public static toUTC(
    year: number,
    month: number,
    date: number,
    hours: number = 0,
    minutes: number = 0,
    seconds: number = 0
  ): Date {
    let idate = new Date(year, month, date, hours, minutes, seconds);
    let utc = Date.UTC(
      idate.getUTCFullYear(),
      idate.getUTCMonth(),
      idate.getUTCDate(),
      idate.getUTCHours(),
      idate.getUTCMinutes(),
      idate.getUTCSeconds());
    return new Date(utc);
  }
}
