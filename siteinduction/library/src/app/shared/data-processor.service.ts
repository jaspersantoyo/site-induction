import { Injectable } from '@angular/core';
import { HttpClient, HttpResponse } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { map, catchError } from 'rxjs/operators';

@Injectable()
export class DataProcessorService {
  private data: Observable<any>;
  private dataProcessorUrl = '/wp-admin/admin-ajax.php?action=request_site_induction_data';

  constructor(
    private http: HttpClient
  ) { }

  public parseData(): Observable<any> {
    if (this.data === undefined) {
      let pData = this.http
        .get(this.dataProcessorUrl)
        .pipe(
          map((response: HttpResponse<any>) => response)
        )
        .pipe(
          catchError(this.handleError)
        );
      this.data = pData;
    }
    return this.data;
  }

  private handleError(error: Response) {
    console.error(error);
    let msg = `Error status code ${error.status} at ${error.url}`;
    return throwError(msg);
  }

}
