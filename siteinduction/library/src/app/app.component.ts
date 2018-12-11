import { Component, OnInit } from '@angular/core';
import { AppData, Wizard, DciAppSteps, LocalStorageKeys, DataMapper } from './core';
import { DataProcessorService } from './shared/data-processor.service';
import { AppService } from './shared/app.service';
import { WizardService } from './shared/wizard.service';
import { LocalStorageService } from './commons';
import { DatePickerService } from './shared/date-picker.service';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.css']
})
export class AppComponent implements OnInit {
  public wizard: Wizard;
  public data: AppData;
  public errorMessage;

  constructor(
    private wizardService: WizardService,
    private dataProcessorService: DataProcessorService,
    private localStorage: LocalStorageService,
    private appService: AppService,
    private datepickerService: DatePickerService,
    private mapper: DataMapper
  ) {
    this.wizard = this.wizardService.wizard;
  }

  public ngOnInit() {
    // Get data using data processor service
    this.dataProcessorService.parseData()
      .subscribe(
        (response) => this.initApp(response),
        (error) => this.errorMessage = error,
    );
  }

  public initApp(response) {
    // Map data to our local variable
    this.data = this.appService.mapAppData(response);

    // Check if there's no error restriction message
    // And also check if we have wizardData stored in local storage
    let saveSess: Wizard = this.localStorage.get(LocalStorageKeys.SESSION);
    if (!this.data.entryFormData.message
      && saveSess) {
      // Get wizardData from local storage
      this.wizard = saveSess;
      this.wizardService.wizard = this.wizard;
    }
    this.datepickerService.formData = this.mapper.mapFormData(this.data.entryFormData);
  }

  /*
  * Returns true if the current step is EntryForm, false otherwise
  */
  public validateDciStep(): boolean {
    return this.wizard.currentStep === DciAppSteps.ENTRY_FORM;
  }
}
