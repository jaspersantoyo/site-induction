import {
  Component,
  OnInit,
  Input
} from '@angular/core';
import { WizardService } from '../shared/wizard.service';
import {
  DciAppSteps,
  Wizard,
  LocalStorageKeys,
  DataMapper,
  DownloadPrint
} from '../core';
import { LocalStorageService } from '../commons';

@Component({
  selector: 'download-print',
  templateUrl: './download-print.component.html',
  styleUrls: ['./download-print.component.scss']
})

export class DownloadPrintComponent implements OnInit {

  @Input()
  public downloadPrintData: any;
  public isFormDownloaded: boolean = false;
  public data: DownloadPrint;
  private wizard: Wizard;

  constructor(
    private wizardService: WizardService,
    private mapper: DataMapper,
    private localStorage: LocalStorageService) {
    this.wizard = this.wizardService.wizard;
  }

  public ngOnInit() {
    this.data = this.mapper.mapDownloadAndPrintData(this.downloadPrintData);
  }

  public downloadFile(): void {
    this.isFormDownloaded = true;
  }

  public isDisable(): boolean {
    return !this.isFormDownloaded;
  }

  public next(): void {
    this.wizardService.nextStep(DciAppSteps.BOOK_TIMESLOT);
    this.localStorage.set(LocalStorageKeys.SESSION, this.wizard);
  }
}
