import { Location } from './location.model';

export class EntryFormFields {

  constructor(
    public fullname: string,
    public firstname: string,
    public lastname: string,
    public email: string,
    public company: string,
    public contractedCompany: string,
    public locations: Location[]
  ) { }
}
