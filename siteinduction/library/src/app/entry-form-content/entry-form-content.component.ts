import { Component, Input } from '@angular/core';
import { EntryFormContent } from '../core';

@Component({
  selector: 'app-entry-form-content',
  templateUrl: './entry-form-content.component.html',
  styleUrls: ['./entry-form-content.component.scss']
})
export class EntryFormContentComponent {
  @Input()
  public content: EntryFormContent;

}
