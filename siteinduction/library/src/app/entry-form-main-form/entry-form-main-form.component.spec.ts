import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { EntryFormMainFormComponent } from './entry-form-main-form.component';

describe('EntryFormMainFormComponent', () => {
  let component: EntryFormMainFormComponent;
  let fixture: ComponentFixture<EntryFormMainFormComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ EntryFormMainFormComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(EntryFormMainFormComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
