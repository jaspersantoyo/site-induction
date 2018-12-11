import { ToLispCasePipe } from './to-lisp-case.pipe';

describe('Pipe: ToLispCase', () => {
  it('create an instance', () => {
    let pipe = new ToLispCasePipe();
    expect(pipe).toBeTruthy();
  });
});
