import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { map, catchError } from 'rxjs/operators';

@Injectable()
export class EmailSendingService {
  // private errorMessage: string;
  private emailProcessorUrl: string = '/wp-admin/admin-ajax.php?action=send_email_confirmation';

  constructor(private http: HttpClient) { }

  public sendEmailConfirmation(submittedData: any): Observable<any> {
    let headers = new HttpHeaders();
    headers.append('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
    return this.http
      .post(this.emailProcessorUrl, submittedData, { headers })
      .pipe(
        map((res) => res)
      )
      .pipe(
        catchError((error) => {
          console.error(error);
          let msg = `Error status code ${error.status} at ${error.url}`;
          return throwError(msg);
        })
      );
  }
}
