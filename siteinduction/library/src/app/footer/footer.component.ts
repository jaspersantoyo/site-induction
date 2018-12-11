import { Component, Input, OnInit } from '@angular/core';
import { Footer } from '../core';

@Component({
  selector: 'app-footer',
  templateUrl: './footer.component.html',
  styleUrls: ['./footer.component.scss']
})
export class FooterComponent implements OnInit {
  @Input()
  public footerData: Footer;
  public copyright: string;
  public links: string;

  public ngOnInit() {
    this.copyright = this.footerData.copyright;
    this.links = this.footerData.footerLinks;
  }

}
