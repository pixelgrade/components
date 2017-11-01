import $ from 'jquery';
import * as Rx from 'rx-dom';
import { BaseComponent } from '../models/DefaultComponent';
import Observable = Rx.Observable;

export const activeClass = 'show-search-overlay';
const openClass = '.js-search-trigger';
const closeClass = '.js-search-close';
const escKeyCode = 27;

export class SearchOverlay extends BaseComponent {

  private $body: JQuery = $( 'body' );
  private $searchField: JQuery = $( '.c-search-overlay' ).find( '.search-field' );
  private openSub: Observable<Event>;
  private closeSub: Observable<Event>;
  private keyupSub: Observable<Event>;
  private subscriptionActive: boolean = true;
  private keyupSubscriptionActive: boolean = true;

  constructor() {
    super();
    this.bindEvents();
  }

  public destroy() {
    this.subscriptionActive = false;
    this.keyupSubscriptionActive = false;
  }

  public bindEvents() {
    this.openSub = Rx.DOM.click(document.querySelector(openClass));
    this.closeSub = Rx.DOM.click(document.querySelector(closeClass));
    this.keyupSub = Rx.DOM.keyup(document.querySelector('body' ));

    this.openSub
        .takeWhile( () => this.subscriptionActive )
        .subscribe( this.open.bind( this ) );

    this.closeSub
        .takeWhile( () => this.subscriptionActive )
        .subscribe( this.close.bind( this ) );
  }

  public createKeyupSubscription() {
    this.keyupSubscriptionActive = true;
    this.keyupSub
        .takeWhile( () => this.keyupSubscriptionActive )
        .subscribe( this.closeOnEsc.bind( this ) );
  }

  public open() {
    this.$searchField.focus();
    this.$body.addClass( activeClass );

    this.createKeyupSubscription();
  }

  public close() {
    this.$body.removeClass( activeClass );
    this.$searchField.blur();
    this.keyupSubscriptionActive = false;
  }

  private closeOnEsc( e: JQuery.Event ) {
    if ( e.keyCode === escKeyCode ) {
      this.close();
    }
  }
}
