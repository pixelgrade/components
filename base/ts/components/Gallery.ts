import { BaseComponent } from '../models/DefaultComponent';
import { JQueryExtended } from '../BaseTheme';

export class Gallery extends BaseComponent {
  protected element: JQueryExtended;

  constructor( element: JQuery ) {
    super();
    this.element = element;

    if ( this.element.is( '.c-gallery--packed, .c-gallery--masonry' ) ) {
      this.layout();
    }
  }

  public bindEvents() {

  }

  public destroy() {

  }

  private layout() {
    const $items = this.element.children();
    let minColumnWidth;

    if ( ! $items.length ) {
      return;
    }

    minColumnWidth = this.element.children().get(0).getBoundingClientRect().width;

    $items.each( (index, element) => {
      const width = element.getBoundingClientRect().width;
      minColumnWidth = width < minColumnWidth ? width : minColumnWidth;
    } );

    this.element.masonry( {
      columnWidth: minColumnWidth,
      transitionDuration: 0,
    } );
  }

}
