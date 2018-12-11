import { TestBed, inject } from '@angular/core/testing';
import { MainContentService } from './main-content.service';

describe('Service: MainContent', () => {
  beforeEach(() => {
    TestBed.configureTestingModule({
      providers: [MainContentService]
    });
  });

  it('should ...', inject([MainContentService], (service: MainContentService) => {
    expect(service).toBeTruthy();
  }));
});
