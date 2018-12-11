import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { map, catchError } from 'rxjs/operators';

@Injectable()
export class WpAdminAjaxService {
  private wpAdminAjaxEndpoint: string = '/wp-admin/admin-ajax.php?action=';

  constructor(private http: HttpClient) { }

  public sendRequest(action: string, submittedData: any): Observable<any> {
    let headers = new HttpHeaders();
    headers.append('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
    return this.http
      .post(this.wpAdminAjaxEndpoint + action, submittedData, { headers })
      .pipe(
        map((res) => res)
      )
      .pipe(
        catchError((error) => {
          console.error(error);
          let msg = `Error status code ${error.status} at ${error.url}`;
          return throwError(msg);
        }));
  }

  public getResource(action: string): Observable<any> {
    return this.http
      .get(this.wpAdminAjaxEndpoint + action)
      .pipe(
        catchError((error) => {
          console.error(error);
          let msg = `Error status code ${error.status} at ${error.url}`;
          return throwError(msg);
        }));

  }
}
