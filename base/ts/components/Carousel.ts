import 'slick-carousel';
import $ from 'jquery';

import { BaseComponent } from '../models/DefaultComponent';
import { JQueryExtended } from '../BaseTheme';
import { Helper } from '../services/Helper';

interface CarouselOptions {
  items_layout?: string;
  items_per_row?: number;
  show_pagination?: string;
}

const variableWidthDefaults = {
  infinite: true,
  slidesToScroll: 1,
  slidesToShow: 1,
  variableWidth: true
};

const fixedWidthDefaults = {
  infinite: false,
  slidesToScroll: 3,
  slidesToShow: 3,
  variableWidth: false,
};

export class Carousel extends BaseComponent {

  private element: JQueryExtended;
  private slickOptions = {
    dots: false,
    nextArrow: '<div class="slick-next"></div>',
    prevArrow: '<div class="slick-prev"></div>',
    speed: 500
  };

  public static customPagination(slider: JQuery, i: number ): JQuery {
    const index = i + 1;
    const sIndex = index <= 9 ? `0${index}` : index;
    return $('<button type="button" />').text( sIndex );
  }

  constructor( element: JQuery, options: CarouselOptions = {} ) {
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
    if ( Helper.above( 'lap' ) ) {
      return this.extendDesktopOptions( options );
    } else {
      return this.extendMobileOptions( options );
    }
  }

  private extendMobileOptions( options: CarouselOptions ) {
    this.slickOptions = Object.assign( {}, this.slickOptions, {
      arrows: false,
      centerMode: true,
      centerPadding: '30px',
      dots: options.show_pagination === '',
      infinite: true,
      slidesToScroll: 1,
      slidesToShow: 1
    });
  }

  private extendDesktopOptions( options: CarouselOptions ) {

    this.slickOptions = Object.assign({}, this.slickOptions, {
      customPaging: Carousel.customPagination
    });

    if ( options.show_pagination === '' ) {
      this.slickOptions.dots = true;
    }

    if ( options.items_layout === 'variable_width' ) {
      this.slickOptions = Object.assign({}, this.slickOptions, variableWidthDefaults);
    } else {
      this.slickOptions = Object.assign({}, this.slickOptions, fixedWidthDefaults);
    }

    if ( options.items_per_row ) {
      this.slickOptions = Object.assign({}, this.slickOptions, {
        slidesToScroll: options.items_per_row,
        slidesToShow: options.items_per_row
      });
    }
  }

  private bindSlick() {
    this.element.slick( this.slickOptions );
  }
}
