import {
  Component,
  OnInit,
  Input,
  AfterViewInit,
  ViewChildren,
  QueryList,
  ChangeDetectorRef
} from '@angular/core';
import { EntryFormValidator } from '../shared/entry-form.validator';
import { WizardService } from '../shared/wizard.service';
import { LocalStorageService } from '../commons';
import {
  EntryFormFields,
  DciAppSteps,
  LocalStorageKeys
} from '../core';
import * as _ from 'lodash';
import { DatePickerService } from '../shared/date-picker.service';
import { FormGroup } from '@angular/forms';
@Component({
  selector: 'app-entry-form-main-form',
  templateUrl: './entry-form-main-form.component.html',
  styleUrls: ['./entry-form-main-form.component.scss']
})
export class EntryFormMainFormComponent implements OnInit, AfterViewInit {

  // TODO: to refactoring prospects, create empty cnstrctr
  @Input()
  public company: string;
  public isFormSubmitted: boolean;
  public data = new EntryFormFields('', '', '', '', '', '', []);
  public errorMessage: string;
  public hasSelectedLocation = false;
  public isFormValid = false;
  public contractedCompanyFlag: boolean = false;

  @ViewChildren('form')
  private companyRef: QueryList<FormGroup>;
  private cookie = 'site-induction';

  constructor(
    private validator: EntryFormValidator,
    private wizardService: WizardService,
    private datepickerService: DatePickerService,
    private localStorage: LocalStorageService,
    private cd: ChangeDetectorRef
  ) { }

  // Initiaze Data
  public ngOnInit() {
    this.data.locations = this.datepickerService.mapLocations();
  }

  public ngAfterViewInit() {
    this.autoPopulateCompanyField();
  }

  // Executes whenever the form is submitted
  public submitForm(e): void {
    e.preventDefault();
    this.isFormSubmitted = true;
    this.data.fullname = this.data.firstname + ' ' + this.data.lastname;
    let valid = this.validator.validateForm(this.data, this.contractedCompanyFlag, this.hasSelectedLocation);
    if (valid) {
      window.scrollTo(0, 0);
      this.wizardService.nextStep(DciAppSteps.MAIN_CONTENT);
      this.localStorage.set(LocalStorageKeys.SESSION, this.wizardService.wizard);
      this.localStorage.set(LocalStorageKeys.USER_FORM, this.data);
      document.cookie = this.cookie + '=' + window.name + '; path=/';
    }
  }

  // Sets the location flag to true if there is atleast 1 location selected
  public checkLocation(): void {
    if (_.find(this.data.locations, (result) => result.selected)) {
      this.hasSelectedLocation = true;
    } else {
      this.hasSelectedLocation = false;
    }
  }

  // Sets the contracted company to true or false;
  public toggleContractedCompany(): void {
    this.contractedCompanyFlag = !this.contractedCompanyFlag;

    // Reset value whenever its toggled to false, in order for the form not to validate
    if (!this.contractedCompanyFlag) {
      this.data.contractedCompany = '';
    }
  }

  // Fill up the company field based on Input variable company
  private autoPopulateCompanyField() {
    if (this.company) {
      this.data.company = this.company;
      this.cd.detectChanges();
      Promise.resolve().then(() => {
        this.companyRef.first.controls['company'].updateValueAndValidity();
        this.companyRef.first.controls['company'].markAsTouched();
      });
    }
  }
}
