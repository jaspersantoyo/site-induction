import { Subtopic } from './subtopic.model';
import { Quiz } from './quiz.model';

export class SectionContent {

  constructor(
    public section: string,
    public checked: boolean,
    public title: string,
    public heading: string,
    public subheading: string,
    public subtopics: Subtopic[],
    public quiz: Quiz[],
  ) { }

}
