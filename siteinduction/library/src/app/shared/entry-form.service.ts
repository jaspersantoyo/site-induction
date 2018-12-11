import { Injectable } from '@angular/core';
import { EntryFormFields, FormData } from '../models';

// TODO: Rename and restructure to proper form validator service
@Injectable()
export class EntryFormService {

  public mapFormData(data): FormData {
    return new FormData(
      data.intellicentres,
      data.intellicentreSettings.general.inspection_days,
      data.intellicentreSettings.general.inspection_hours,
      data.intellicentreSettings.general.holidays,
      data.intellicentreSettings.general.facility_servicing_contractor
    );
  }

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
