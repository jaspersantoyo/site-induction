import { Component, OnInit, Input } from '@angular/core';
import { WizardService } from '../shared/wizard.service';
import { EntryFormContent, FormData, DataMapper } from '../core';

@Component({
  selector: 'app-entry-form',
  templateUrl: './entry-form.component.html',
  styleUrls: ['./entry-form.component.scss']
})
export class EntryFormComponent implements OnInit {
  @Input() public entryFormData: any;

  public isMobile: boolean;
  public entryFormContent: EntryFormContent;
  public formData: FormData;
  public message: string;

  constructor(
    private wizardService: WizardService,
    private mapper: DataMapper
  ) { }

  public ngOnInit() {
    this.entryFormContent = this.entryFormData.entryFormContent;
    this.formData = (!this.entryFormData.message) ? this.mapper.mapFormData(this.entryFormData) : null;
    this.isMobile = this.wizardService.checkIsMobile(window.innerWidth);
    this.message = this.entryFormData.message;
  }

  public onScreenResize(event) {
    this.isMobile = this.wizardService.checkIsMobile(event.target.innerWidth);
  }

}
