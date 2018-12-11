import { GeneralSettings } from './general-settings.model';
import { ProcessSections } from './process-sections.model';
import { Summary } from './summary.model';

export class MainContentData {

  constructor(
    public generalSettings: GeneralSettings,
    public processSections: ProcessSections[],
    public summary: Summary
  ) { }

}
