import { Injectable } from '@angular/core';
import { AppData } from '../core';

@Injectable()
export class AppService {

  public mapAppData(response: any): AppData {
    let entryFormData = {
      'entryFormContent': response.entryFormData,
      'intellicentres': response.intellicentres,
      'intellicentreSettings': response.intellicentreSettings,
      'message': response.message
    };

    let mainContentData = {
      'generalSettings': response.generalSettings,
      'processSections': response.intellicentreData,
      'downloadPrintData': response.downloadAndPrintData,
      'summaryData': response.summaryData
    };

    let footerData = response.footer;

    return new AppData(entryFormData, mainContentData, footerData);
  }
}
