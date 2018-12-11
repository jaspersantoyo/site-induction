import { Question } from './question.model';

export class Quiz {

  constructor(
    public category: string,
    public questions: Question[]
  ) { }

}
