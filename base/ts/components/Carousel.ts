import 'slick-carousel';

import { BaseComponent } from '../models/DefaultComponent';
import { JQueryExtended } from '../BaseTheme';

interface CarouselOptions {
  items_layout?: string;
  items_per_row?: number;
}

export class Carousel extends BaseComponent {

  private element: JQueryExtended;
  private slickOptions = {
    dots: true,
    infinite: false,
    nextArrow: '<div class="slick-next"></div>',
    prevArrow: '<div class="slick-prev"></div>',
    slidesToScroll: 3,
    slidesToShow: 3,
    speed: 500,
    variableWidth: true
  };

  constructor( element: JQuery, options: CarouselOptions ) {
    super();
    this.element = element;

    this.extendOptions( options );
    this.bindEvents();
  }

  public bindEvents() {
    this.bindSlick();
  }

  public destroy() {
    this.element.slick('unslick');
  }

  private extendOptions( options: CarouselOptions ) {
    if ( options.items_layout ) {
      this.slickOptions.variableWidth = options.items_layout === 'variable_width';
    }

    if ( options.items_per_row ) {
      this.slickOptions.slidesToShow = options.items_per_row;
      this.slickOptions.slidesToScroll = options.items_per_row;
    }
  }

  private bindSlick() {
    this.element.slick( this.slickOptions );
  }
}
