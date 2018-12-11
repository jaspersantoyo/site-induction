import { Appointment } from './appointment.model';

export class Summary {

  constructor(
    public heading: string,
    public successMessage: string,
    public finishButtonLink: string,
    public uuid: string,
    public appointment: Appointment[]
  ) { }

}
