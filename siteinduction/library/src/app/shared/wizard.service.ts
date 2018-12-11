import { Injectable } from '@angular/core';
import { Wizard, DciAppSteps } from '../core';

@Injectable()
export class WizardService {

  /**
   * GET/SET Wizard Data
   */
  private _wizard: Wizard;
  public get wizard(): Wizard {
    return this._wizard;
  }
  public set wizard(val: Wizard) {
    this._wizard = val;
  }

  constructor() {
    this._wizard = new Wizard(DciAppSteps.ENTRY_FORM, '', '');
  }

  public getWizardData() {
    return this._wizard;
  }

  public nextStep(step: string): void {
    this._wizard.currentStep = step;
  }

  public nextLocation(location: string): void {
    this._wizard.currentLocation = location;
  }

  public nextSection(section: string): void {
    this._wizard.currentSection = section;
  }

  public updateWizard(step: string, location: string, section: string): void {
    this.nextStep(step);
    this.nextLocation(location);
    this.nextSection(section);
  }

  public checkIsMobile(screenSize): boolean {
    return (screenSize < 768) ? true : false;
  }

}
