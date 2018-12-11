import { Injectable } from '@angular/core';
import { Quiz, Question } from '../core';
import * as _ from 'lodash';

@Injectable()
export class MainContentService {

  public validateMultipleChoiceQuestions(questions: Question[]): number {
    let incorrectAnswers = 0;
    for (let question of questions) {
      if (question.type === 'multiple_choice_question') {
        question.validate = true;
        if (question.usersAnswer !== question.correctAnswer) {
          incorrectAnswers += 1;
        }
      }
    }
    return incorrectAnswers;
  }

  public validateQuiz(data: Quiz[]): boolean {
    let isValid = true;
    for (let quiz of data) {
      _.map(quiz.questions, (question) => {
        question.validate = true;
        return question;
      });
      if (_.find(quiz.questions, { 'usersAnswer': '' })
        || _.find(quiz.questions, { 'isCorrect': false })) {
        isValid = false;
      }
    }
    return isValid;
  }
}
