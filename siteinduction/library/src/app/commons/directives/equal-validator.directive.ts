import {
  NG_VALIDATORS,
  FormControl,
  ValidatorFn,
  Validator
} from '@angular/forms';
import {
  Directive,
  Input
} from '@angular/core';

/* This is a validation directive for similar input on text box.
*  Value in the input field where this directive is used is compared into a passed parameter in this directive.
*/
@Directive({
  selector: '[equalValidator][ngModel]',
  providers: [
    {
      provide: NG_VALIDATORS,
      useExisting: EqualValidator,
      multi: true
    }
  ]
})

export class EqualValidator implements Validator {
  @Input()
  public equalValidator = '';
  private validator: ValidatorFn;
  constructor() {
    this.validator = this.equalValidatorFn();
  }

  public validate(c: FormControl) {
    return this.validator(c);
  }

  public equalValidatorFn(): ValidatorFn {
    return (c: FormControl) => {
      let value = c.value ? c.value : '';
      let isValid = this.equalValidator.toLowerCase().trim() !== value.toLowerCase().trim();
      if (isValid) {
        return null;
      } else {
        return {
          equalValidator: {
            valid: false
          }
        };
      }
    };
  }
}
