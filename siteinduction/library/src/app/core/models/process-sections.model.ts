import { SectionContent } from './section-content.model';

export class ProcessSections {

  constructor(
    public location: string,
    public introduction: string,
    public sections: SectionContent[]
  ) { }

}
