import { TestBed, inject } from '@angular/core/testing';
import { WpAdminAjaxService } from './wp-admin-ajax.service';

describe('Service: EmailSending', () => {
  beforeEach(() => {
    TestBed.configureTestingModule({
      providers: [WpAdminAjaxService]
    });
  });

  it('should ...', inject([WpAdminAjaxService], (service: WpAdminAjaxService) => {
    expect(service).toBeTruthy();
  }));
});
