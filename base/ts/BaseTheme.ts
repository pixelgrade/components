import $ from 'jquery';

import { Helper } from './services/Helper';
import { WindowService } from './services/window.service';
import { GlobalService } from './services/global.service';

export interface JQueryExtended extends JQuery {
  imagesLoaded?( params: any );
  masonry?( options?: any, elements?: any, isStill?: boolean );
  select2?( params?: any );
  slick?( params?: any );
}

export class BaseTheme {

  public $body: JQuery = $( 'body' );
  public $html: JQuery = $( 'html' );
  public ev: JQuery = $( {} );
  public frameRendered: boolean = false;

  public subscriptionActive: boolean = true;

  constructor() {
    this.$html.toggleClass( 'is-IE', Helper.getIEversion() && Helper.getIEversion() < 12 );

    this.bindEvents();
    this.renderLoop();
  }

  public bindEvents(): void {
    GlobalService.onReady().take(1).subscribe(this.onReadyAction.bind(this));
    WindowService.onLoad().take(1).subscribe(this.onLoadAction.bind(this));
    WindowService.onResize().debounce(300).subscribe(this.onResizeAction.bind(this));
    WindowService.onScroll().subscribe(() => {
      this.frameRendered = false;
    });

    // Leave comments area visible by default and
    // show it only if the URL links to a comment
    if ( window.location.href.indexOf( '#comment' ) === -1 ) {
      $( '.trigger-comments' ).removeAttr( 'checked' );
    }
    $( window ).on( 'beforeunload', this.fadeOut.bind(this) );

    this.ev.on( 'render', this.update.bind(this) );
  }

  public onReadyAction() {
    this.$html.addClass('is-ready');
  }

  public onLoadAction() {
    this.$html.addClass('is-loaded');
    this.fadeIn();
  }

  public onResizeAction() {}

  public destroy(): void {
    this.subscriptionActive = false;
  }

  public renderLoop() {
    if ( this.frameRendered === false ) {
      this.ev.trigger( 'render' );
    }
    requestAnimationFrame( () => {
      this.renderLoop();
      this.frameRendered = true;
      this.ev.trigger( 'afterRender' );
    } );
  }

  public update() {
    this.backToTop();
  }

  public backToTop() {
    const opacity = WindowService.getScrollY() >= WindowService.getHeight() ? 1 : 0;
    $( '.back-to-top' ).css( 'opacity', opacity );
  }

  public eventHandlers( $container ) {
    $container.find( '.back-to-top' ).on( 'click', ( e ) => {
      e.preventDefault();
      Helper.smoothScrollTo( 0, 1000 );
    } );

    $container.find( '.widget_categories select' ).select2();
  }

  public fadeOut() {
    this.$html.removeClass( 'fade-in' ).addClass( 'fade-out' );
  }

  public fadeIn() {
    this.$html.removeClass( 'fade-out no-transitions' ).addClass( 'fade-in' );
  }
}
