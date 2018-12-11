import {
  Component,
  Input,
  OnInit
} from '@angular/core';

import {
  trigger,
  state,
  style,
  transition,
  animate
} from '@angular/animations';

import {
  Wizard,
  MainContentData,
  GeneralSettings,
  Options,
  EntryFormFields,
  ProcessSections,
  SectionContent,
  Subtopic,
  Quiz,
  Question,
  DciAppSteps,
  LocalStorageKeys
} from '../core';
import { WizardService } from '../shared/wizard.service';
import { MainContentService } from '../shared/main-content.service';
import * as _ from 'lodash';
import { LocalStorageService } from '../commons';

@Component({
  selector: 'app-main-content',
  templateUrl: './main-content.component.html',
  styleUrls: ['./main-content.component.scss'],
  animations: [
    trigger('navToggle', [
      state('in', style({
        transform: 'translate3d(-100%, 0, 0)'
      })),
      state('out', style({
        transform: 'translate3d(0, 0, 0)'
      })),
      transition('in => out', animate('200ms ease-in-out')),
      transition('out => in', animate('200ms ease-in-out'))
    ]),
  ]
})
export class MainContentComponent implements OnInit {
  @Input()
  public mainContentData: MainContentData;
  @Input()
  public formData: any;

  public wizard: Wizard;
  public errorMessage: string;
  public settings: GeneralSettings;
  public options: Options;
  public sections: ProcessSections[];
  public navigation;
  public isMobile: boolean;
  public navState = 'in';
  public entryFormData: EntryFormFields;
  public steps: any = {};

  constructor(
    private localStorage: LocalStorageService,
    private wizardService: WizardService,
    private mainContentService: MainContentService,
  ) {
    this.wizard = this.wizardService.wizard;
    this.steps = {
      SUMMARY: DciAppSteps.SUMMARY,
      MAIN_CONTENT: DciAppSteps.MAIN_CONTENT,
      DOWNLOAD_PRINT: DciAppSteps.DOWNLOAD_PRINT,
      BOOK_TIMESLOT: DciAppSteps.BOOK_TIMESLOT
    };
  }

  public ngOnInit() {
    this.entryFormData = this.localStorage.get(LocalStorageKeys.USER_FORM);
    this.options = new Options(true, true, false);

    this.sections = this.localStorage.get(LocalStorageKeys.SECTIONS) ?
      this.localStorage.get(LocalStorageKeys.SECTIONS) : [];

    this.navigation = (this.localStorage.get(LocalStorageKeys.NAVIGATION)) ?
      this.localStorage.get(LocalStorageKeys.NAVIGATION) : {};

    this.settings = this.mainContentData.generalSettings;
    if (this.sections.length === 0) {
      this.mapProcessSections(this.mainContentData.processSections);
      this.initWizardData();
      this.localStorage.set(LocalStorageKeys.NAVIGATION, this.navigation);
    }

    this.isMobile = this.wizardService.checkIsMobile(window.innerWidth);
  }

  public onScreenResize(event) {
    this.isMobile = this.wizardService.checkIsMobile(event.target.innerWidth);
  }

  public toggleNav() {
    this.navState = this.navState === 'out' ? 'in' : 'out';
  }

  public mapProcessSections(locationData): void {
    for (let data of locationData) {
      if (_.find(this.entryFormData.locations, { 'title': data.intellicentre, 'selected': true })) {
        let sectionsData = [];
        let mappedData: SectionContent;
        let sections = [];

        if (data.sections) {
          for (let section of data.sections) {
            mappedData = this.mapSectionData(section);
            sectionsData.push(mappedData);
            sections.push(mappedData.heading);
          }
        }

        this.navigation[data.intellicentre] = sections;
        this.sections.push(
          new ProcessSections(data.intellicentre, data.introduction, sectionsData));
      }
    }
  }

  public mapSectionData(section): SectionContent {
    let sectionContent: SectionContent;

    if (section.acf_fc_layout === 'topic_section') {
      sectionContent = new SectionContent(
        section.acf_fc_layout,
        false,
        section.topic_title,
        section.topic_title,
        '',
        this.mapSubtopicsData(section.sub_topic),
        []
      );
    } else {
      sectionContent = new SectionContent(
        section.acf_fc_layout,
        false,
        'Quiz',
        section.quiz_heading,
        section.quiz_sub_heading,
        [],
        this.mapQuizData(section.question_category)
      );
    }

    return sectionContent;
  }

  public initWizardData(): void {
    if (!this.wizard.currentLocation) {
      this.wizardService.nextLocation(this.sections[0].location);
    }

    if (!this.wizard.currentSection && this.sections[0].sections.length > 0) {
      this.wizardService.nextSection(this.sections[0].sections[0].heading);
    }

    this.localStorage.set(LocalStorageKeys.SESSION, this.wizard);
  }

  public nextSection(currentLocation: string, currentSection: string): void {
    let nextSection = this.getNext(this.navigation[currentLocation], currentSection);

    if (nextSection) {
      this.wizardService.nextSection(nextSection);
      this.localStorage.set(LocalStorageKeys.SESSION, this.wizard);
    }
    window.scrollTo(0, 0);
  }

  public viewSection(location: string, section: SectionContent): void {
    if (location === this.wizard.currentLocation && section.checked) {
      this.wizardService.nextSection(section.heading);
    }
  }

  public getNext(array: string[], current: string): string {
    let isNext = false;
    let next: string;

    let cli = _.findIndex(this.sections, ['location', this.wizard.currentLocation]);
    let csi = _.findIndex(this.sections[cli]['sections'], ['heading', this.wizard.currentSection]);
    this.sections[cli]['sections'][csi]['checked'] = true;

    if (current === this.wizard.currentSection) {
      let nextIndex = array.indexOf(current) + 1;
      next = (array[nextIndex]) ? array[nextIndex] : '';
    } else {
      for (let key of Object.keys(array)) {
        if (isNext) {
          isNext = false;
          next = key;
        }
        if (key === current) {
          isNext = true;
        }
      }
    }
    this.localStorage.set(LocalStorageKeys.SECTIONS, this.sections);
    return next;
  }

  public selectedAnswer(question: Question): void {
    question.validate = false;
    question.isCorrect = question.type === 'multiple_choice_question'
      && question.usersAnswer === question.correctAnswer;
  }

  public submitQuiz(e, quiz: Quiz[]): void {
    e.preventDefault();

    this.options.isQuizSubmitted = true;
    let quizData: any = (this.localStorage.get(LocalStorageKeys.QUIZ)) ?
      this.localStorage.get(LocalStorageKeys.QUIZ) : {};
    this.focusFirstIncorrectItem(e.target);
    if (this.mainContentService.validateQuiz(quiz)) {
      window.scrollTo(0, 0);
      quizData[this.wizard.currentLocation] = quiz;

      let nextLocation = this.getNext(this.navigation, this.wizard.currentLocation);
      if (nextLocation) {
        this.wizardService.nextLocation(nextLocation);
        this.wizardService.nextSection(this.navigation[nextLocation][0]);
      } else {
        this.wizardService.updateWizard(DciAppSteps.DOWNLOAD_PRINT, '', '');
      }

      this.localStorage.set(LocalStorageKeys.QUIZ, quizData);
      this.localStorage.set(LocalStorageKeys.SESSION, this.wizard);
      this.options.isQuizSubmitted = false;
    }
  }

  public checkCurrentStepIfDone(step: string, stepIndex?: number): boolean {
    switch (step) {
      case DciAppSteps.MAIN_CONTENT:

        if (!this.wizardService.wizard.currentLocation) {
          return true;
        }
        // Check location index if less than the current location
        let currentLocationIndex: number = 0;
        this.sections.forEach((section, index) => {
          if (section.location === this.wizardService.wizard.currentLocation) {
            currentLocationIndex = index;
          }
        });

        if (stepIndex < currentLocationIndex) {
          return true;
        }
        return false;
      case DciAppSteps.DOWNLOAD_PRINT:
        if (this.wizardService.wizard.currentStep === DciAppSteps.SUMMARY ||
          this.wizardService.wizard.currentStep === DciAppSteps.BOOK_TIMESLOT) {
          return true;
        }
        return false;
      case DciAppSteps.BOOK_TIMESLOT:
        if (this.wizardService.wizard.currentStep === DciAppSteps.SUMMARY) {
          return true;
        }
      default:
        return false;
    }
  }

  /*
  * Returns true if the current dci step is equal to the passed string value, false otherwise
  */
  public validateDciStep(step: string): boolean {
    return this.wizard.currentStep === step;
  }

  /*
  * Create proper numbering for SubContent Heading
  */
  public createSubContentHeadingNumbering(sectionIndex: number, subheadingIndex: number): string {

    // convert number into letters, 97 is letter a in ASCII, b will be 98 and so on..
    let subheadingNumber: string = String.fromCharCode(97 + subheadingIndex);
    let sectionNumber: number = sectionIndex + 1;

    return sectionNumber  + subheadingNumber + ') ';
  }

  /*
  * Go to Download and Print Step
  */
  public goToDownloadAndPrint(): void {
    if (this.wizardService.wizard.currentStep === DciAppSteps.BOOK_TIMESLOT) {
      this.wizardService.nextStep(DciAppSteps.DOWNLOAD_PRINT);
    }
  }

  /*
  * Exits the App, will clear all save data in local storage and reload the page.
  */
  public exitApp(e): void {
    e.preventDefault();
    if (confirm('If you exit, you will lose all progress. Are you sure?')) {
      this.localStorage.clearAll();
      window.location.reload();
    }
  }

  private focusFirstIncorrectItem(elements: any): void {
    let bodyRect = document.body.getBoundingClientRect();
    for (let element of elements) {
      if (element.classList.contains('incorrect')) {
        let elementRect = this.getParentElement(element, 'quiz-question').getBoundingClientRect();
        let offset = elementRect.top - bodyRect.top;
        if (this.isMobile) {
          offset -= 90;
        }
        window.scrollTo(0, offset);
        return;
      }
    }
  }

  private mapSubtopicsData(subtopics): Subtopic[] {
    let subtopicsData: Subtopic[] = [];

    for (let subtopic of subtopics) {
      subtopicsData.push(new Subtopic(subtopic.sub_topic_title, subtopic.sub_topic_content));
    }

    return subtopicsData;
  }

  private mapQuizData(quizzes): Quiz[] {
    let quizData: Quiz[] = [];

    for (let quiz of quizzes) {
      quizData.push(new Quiz(quiz.category_name, this.mapQuizQuestions(quiz.question_type)));
    }

    return quizData;
  }

  private mapQuizQuestions(questions): Question[] {
    let quizQuestions: Question[] = [];
    let correctAnswer: string;
    let choices: string[];
    for (let question of questions) {
      if (question.acf_fc_layout === 'multiple_choice_question') {
        choices = question.choices;
        for (let choice of question.choices) {
          if (choice.correct_answer) {
            correctAnswer = choice.choice;
          }
        }
      } else {
        choices = [];
        correctAnswer = question.answer;
      }

      quizQuestions.push(
        new Question(question.acf_fc_layout, question.question, choices, correctAnswer, '', null, false));
    }

    return quizQuestions;
  }

  private getParentElement(element: any, targetClass: string): any {
    let parentElement: any = null;
    if (element.parentElement.classList.contains(targetClass)) {
      parentElement = element.parentElement;
    } else {
      parentElement = this.getParentElement(element.parentElement, targetClass);
    }
    return parentElement;
  }
}
