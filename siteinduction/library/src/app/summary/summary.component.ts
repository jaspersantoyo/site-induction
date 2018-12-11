import { Component, Input, OnInit } from '@angular/core';
import { EntryFormFields, Quiz, Summary, LocalStorageKeys } from '../core';
import { SummaryPageService } from '../shared/summary-page.service';
import { LocalStorageService } from '../commons';
import { WpAdminAjaxService } from '../shared/wp-admin-ajax.service';

@Component({
  selector: 'app-summary',
  templateUrl: './summary.component.html',
  styleUrls: ['./summary.component.scss']
})
export class SummaryComponent implements OnInit {
  @Input()
  public summaryData;

  public summary: Summary;

  private submittedData: any;
  private wpAjaxAction = 'process_dci_data';

  constructor(
    private summaryPageService: SummaryPageService,
    private localStorage: LocalStorageService,
    private wpAdminAjaxService: WpAdminAjaxService
  ) { }

  public ngOnInit() {
    this.setSummaryData(this.summaryData);
  }

  public setSummaryData(summaryData): void {
    let entryFormData: EntryFormFields = this.localStorage.get(LocalStorageKeys.USER_FORM);
    let appointment = this.summaryPageService.mapAppointment(entryFormData);
    let quizData: Quiz[] = this.localStorage.get(LocalStorageKeys.QUIZ);
    this.summary = this.summaryPageService.mapSummaryData(summaryData, appointment);
    this.submittedData = this.summaryPageService.mapUserData(entryFormData, quizData, this.summary);
    this.wpAdminAjaxService.sendRequest(this.wpAjaxAction, this.submittedData).subscribe(
      (res) => console.log(res)
    );
    this.localStorage.clearAll();
  }
}
