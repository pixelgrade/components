import $ from 'jquery';
import * as Rx from 'rx-dom';
import { BaseComponent } from '../models/DefaultComponent';
import Observable = Rx.Observable;

const activeClass = 'show-search-overlay';
const openClass = '.js-search-trigger';
const closeClass = '.js-search-close';
const escKeyCode = 27;

export class SearchOverlay extends BaseComponent {

  private $body: JQuery = $( 'body' );
  private $document: JQuery<Document> = $( document );
  private $searchField: JQuery = $( '.c-search-overlay' ).find( '.search-field' );
  private keyupSub: Observable<Event>;
  private keyupSubscriptionActive: boolean = true;

  constructor() {
    super();
    this.bindEvents();
  }

  public destroy() {
    this.keyupSubscriptionActive = false;
    this.$document.off( 'click.SearchOverlay' );
  }

  public bindEvents() {
    this.$document.on( 'click.SearchOverlay', openClass, this.open.bind( this ) );
    this.$document.on( 'click.SearchOverlay', closeClass, this.close.bind( this ) );

    this.keyupSub = Rx.DOM.keyup(document.querySelector('body' ));
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
