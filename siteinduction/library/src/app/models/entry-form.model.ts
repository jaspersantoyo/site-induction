import { EntryFormFields } from './entry-form-fields.model';
import { EntryFormContent } from './entry-form-content.model';

export class EntryFormData {

  constructor(
    public entryFormContent: EntryFormContent,
    public entryFormFields: EntryFormFields
  ) { }

}
