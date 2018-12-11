export class Question {

  constructor(
    public type: string,
    public question: string,
    public choices: string[],
    public correctAnswer: string,
    public usersAnswer: string,
    public isCorrect: boolean,
    public validate: boolean
  ) { }

}
