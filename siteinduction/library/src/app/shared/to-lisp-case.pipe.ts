import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'toLispCase'
})
export class ToLispCasePipe implements PipeTransform {

  public transform(value: string): string {
    if (!value) {
      return value;
    }
    return value.replace(' ', '-').toLowerCase();
  }

}
