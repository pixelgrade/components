import $ from 'jquery';
import 'slick-carousel';

import { BaseComponent } from '../models/DefaultComponent';
import { JQueryExtended } from '../BaseTheme';

export class Slideshow extends BaseComponent {
  private blendedSelector: string = '.blend-with-header';
  private slideshowWidgetSelector: string = '.widget_featured_posts_slideshow';
  private $siteHeader: JQuery = $( '.site-header' );
  private headerBlendedClass: string = 'site-header--blended';

  private element: JQueryExtended;

  private slickOptions = {
    customPaging: (slider, i) => {
      const index = i < 9 ? '0' + i : i;
      return '<a>' + index + '</a>';
    },
    dots: false,
    infinite: true,
    nextArrow: '<div class="slick-next"></div>',
    prevArrow: '<div class="slick-prev"></div>',
    speed: 500,
  };

  constructor( element ) {
    super();

    this.element = element;

    this.maybeBlendHeader();
    this.bindEvents();
  }

  public bindEvents() {
   this.bindSlick();
  }

  public destroy() {
    this.element.slick('unslick');
  }

  private maybeBlendHeader() {
    const $firstWidget = this.element.filter( this.blendedSelector ).closest( this.slideshowWidgetSelector ).first();
    const $siteHeader = this.$siteHeader;

    if ( $firstWidget.length ) {
      $firstWidget.appendTo( this.$siteHeader );
      $siteHeader.addClass( this.headerBlendedClass );
    }
  }

  private bindSlick() {
    const that: Slideshow = this;

    this.element.each((i, obj) => {
      const $element = $(obj);
      const $slider = $element.find( '.c-hero__slider' );
      const slickOptions = that.slickOptions;

      slickOptions.dots = typeof $element.data('show_pagination') !== 'undefined';

      ( $slider as JQueryExtended ).slick( slickOptions );
    });
  }
}
