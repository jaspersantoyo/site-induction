import { TestBed, inject } from '@angular/core/testing';
import { DataProcessorService } from './data-processor.service';

describe('Service: DataProcessor', () => {
  beforeEach(() => {
    TestBed.configureTestingModule({
      providers: [DataProcessorService]
    });
  });

  it('should ...', inject([DataProcessorService], (service: DataProcessorService) => {
    expect(service).toBeTruthy();
  }));
});
