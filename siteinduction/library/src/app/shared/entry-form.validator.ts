import { Injectable } from '@angular/core';
import { EntryFormFields } from '../core';

// TODO: Rename and restructure to proper form validator service
@Injectable()
export class EntryFormValidator {

  public validateForm(
    model: EntryFormFields,
    contractedCompany: boolean,
    hasSelectedLocation: boolean): boolean {
    let isFormValid: boolean = true;

    let emailPattern = ['^(([^<>()\\[\\]\\\.,;:\\s@"]+(\.[^<>()\\[\\]\\\.,;:\\s@"]+)*)',
      '|(".+"))@((\\[[0-9]{1,3}\\.[0-9]{1,3}\\.[0-9]{1,3}\\.[0-9]{1,3}])',
      '|(([a-zA-Z\\-0-9]+\\.)+[a-zA-Z]{2,}))$'].join('');

    let regEx = new RegExp(emailPattern);

    // TODO: Checks can be merge
    if (model.firstname === undefined
      || model.lastname === undefined
      || model.company === undefined) {
      isFormValid = false;
    }

    if (contractedCompany && model.contractedCompany === undefined
      && model.contractedCompany === '') {
      isFormValid = false;
    }

    if (!regEx.test(model.email)) {
      isFormValid = false;
    }

    if (!hasSelectedLocation) {
      isFormValid = false;
    }

    return isFormValid;
  }
}
