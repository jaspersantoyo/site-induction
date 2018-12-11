import { TestBed, inject } from '@angular/core/testing';
import { SummaryPageService } from './summary-page.service';

describe('Service: SummaryPage', () => {
  beforeEach(() => {
    TestBed.configureTestingModule({
      providers: [SummaryPageService]
    });
  });

  it('should ...', inject([SummaryPageService], (service: SummaryPageService) => {
    expect(service).toBeTruthy();
  }));
});
