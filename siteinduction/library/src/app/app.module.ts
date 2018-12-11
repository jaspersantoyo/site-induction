import { BrowserModule } from '@angular/platform-browser';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { NgModule } from '@angular/core';
import { HttpClientModule } from '@angular/common/http';
import { FormsModule } from '@angular/forms';
import { MyDatePickerModule } from 'mydatepicker';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';

import { AppComponent } from './app.component';
import { EntryFormComponent } from './entry-form/entry-form.component';
import { EntryFormContentComponent } from './entry-form-content/entry-form-content.component';
import { EntryFormMainFormComponent } from './entry-form-main-form/entry-form-main-form.component';
import { MainContentComponent } from './main-content/main-content.component';
import { SummaryComponent } from './summary/summary.component';
import { LoadingScreenComponent } from './loading-screen/loading-screen.component';
import { FooterComponent } from './footer/footer.component';
import { BookTimeslotComponent } from './book-timeslot/book-timeslot.component';
import { DownloadPrintComponent } from './download-print/download-print.component';

import { WizardService } from './shared/wizard.service';
import { AppService } from './shared/app.service';
import { EntryFormValidator } from './shared/entry-form.validator';
import { DataProcessorService } from './shared/data-processor.service';
import { MainContentService } from './shared/main-content.service';
import { SummaryPageService } from './shared/summary-page.service';
import { WpAdminAjaxService } from './shared/wp-admin-ajax.service';
import { DatePickerService } from './shared/date-picker.service';
import {
  LocalStorageService,
  DateUtil,
  EqualValidator
} from './commons';
import { DataMapper } from './core';
import { ToLispCasePipe } from './shared/to-lisp-case.pipe';

/**
 * Styling SASS
 */
import '../styles/base.scss';

@NgModule({
  declarations: [
    AppComponent,
    EntryFormComponent,
    EntryFormContentComponent,
    EntryFormMainFormComponent,
    MainContentComponent,
    SummaryComponent,
    LoadingScreenComponent,
    FooterComponent,
    BookTimeslotComponent,
    DownloadPrintComponent,
    EqualValidator,
    ToLispCasePipe
  ],
  imports: [
    BrowserModule,
    FormsModule,
    BrowserAnimationsModule,
    HttpClientModule,
    MyDatePickerModule,
    NgbModule.forRoot()
  ],
  providers: [
    DataProcessorService,
    WizardService,
    EntryFormValidator,
    MainContentService,
    SummaryPageService,
    WpAdminAjaxService,
    AppService,
    DatePickerService,
    LocalStorageService,
    DateUtil,
    DataMapper
  ],
  bootstrap: [AppComponent]
})
export class AppModule { }
