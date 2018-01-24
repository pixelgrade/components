import $ from 'jquery';
import * as imagesLoaded from 'imagesloaded';
import 'jquery-hoverintent';
import { BaseComponent } from '../../base/ts/models/DefaultComponent';
import { Helper } from '../../base/ts/services/Helper';
import { WindowService } from '../../base/ts/services/window.service';

interface JQueryExtended extends JQuery {
  hoverIntent?( params: any ): void;
  imagesLoaded?( params: any );
}

export class Header extends BaseComponent {

  private $body: JQuery = $( 'body' );
  private $document: JQuery = $( document );
  private $mainMenu: JQuery = $( '.menu--primary' );
  private $mainMenuItems: JQueryExtended = this.$mainMenu.find( 'li' );
  private $menuToggle: JQuery = $( '#menu-toggle' );
  private isMobileHeaderInitialised: boolean = false;
  private isDesktopHeaderInitialised: boolean = false;
  private areMobileBindingsDone: boolean = false;
  private subscriptionActive: boolean = true;
  private preventOneSelector: string = 'a.prevent-one';

  constructor() {
    super();

    imagesLoaded( $( '.c-navbar .c-logo' ), () => {

      this.bindEvents();
      this.eventHandlers();
      this.updateOnResize();

    });
  }

  public destroy() {
    this.subscriptionActive = false;
  }

  public bindEvents() {

    this.$menuToggle.on( 'change', this.onMenuToggleChange.bind(this));

    this.$mainMenuItems.hoverIntent( {
      out: (e) => this.toggleSubMenu(e, false),
      over: (e) => this.toggleSubMenu(e, true),
      timeout: 300
    } );

    WindowService
      .onResize()
      .takeWhile( () => this.subscriptionActive )
      .subscribe( () => {
        this.updateOnResize();
      } );
  }

  public eventHandlers() {
    if ( Helper.below( 'lap' ) && !this.areMobileBindingsDone ) {
      this.$document.on( 'click', this.preventOneSelector, this.onMobileMenuExpand.bind(this) );
      this.areMobileBindingsDone = true;
    }

    if ( Helper.above( 'lap' ) && this.areMobileBindingsDone ) {
      this.$document.off( 'click', this.preventOneSelector, this.onMobileMenuExpand.bind(this) );
      this.areMobileBindingsDone = false;
    }
  }

  private updateOnResize() {
    this.eventHandlers();

    if ( Helper.below( 'lap' ) ) {
      this.prepareMobileMenuMarkup();
    } else {
      this.prepareDesktopMenuMarkup();
    }
  }

  private prepareDesktopMenuMarkup(): void {
    if ( this.isDesktopHeaderInitialised ) {
      return;
    }

    this.isDesktopHeaderInitialised = true;
  }

  private prepareMobileMenuMarkup(): void {
    // If if has not been done yet, prepare the mark-up for the mobile navigation
    if ( this.isMobileHeaderInitialised ) {
      return;
    }

    // Append the branding
    const $branding = $( '.c-branding' );
    $branding.clone().addClass('c-branding--mobile');
    $branding.find( 'img' ).removeClass( 'is--loading' );

    // Create the mobile site header
    const $siteHeaderMobile = $( '<div class="site-header-mobile  u-header-sides-spacing"></div>' ).appendTo( '.c-navbar' );

    // Append the social menu
    const $searchTrigger = $( '.js-mobile-search-trigger' ).clone().show();

    $siteHeaderMobile.append( $branding );
    $siteHeaderMobile.append( $searchTrigger );
    $siteHeaderMobile.appendTo( '.c-navbar' );

    // Handle sub menus:
    // Make sure there are no open menu items
    $( '.menu-item-has-children' ).removeClass( 'hover' );

    // Add a class so we know the items to handle
    $( '.menu-item-has-children > a' ).each( ( index, element ) => {
      $( element ).addClass( 'prevent-one' );
    } );

    // Replace the label text and make it visible
    $( '.c-navbar__label-text ' ).html( $( '.js-menu-mobile-label' ).html() ).removeClass( 'screen-reader-text' );

    this.isMobileHeaderInitialised = true;
  }

  private toggleSubMenu(e: JQuery.Event, toggle: boolean) {
    $( e.currentTarget ).toggleClass( 'hover', toggle );
  }

  private onMobileMenuExpand(e: JQuery.Event): void {
    e.preventDefault();
    e.stopPropagation();

    const $button = $( e.currentTarget );
    const activeClass = 'active';
    const hoverClass = 'hover';

    if ( $button.hasClass( activeClass ) ) {
      window.location.href = $button.attr( 'href' );
      return;
    }

    $( this.preventOneSelector ).removeClass( activeClass );
    $button.addClass( activeClass );

    // When a parent menu item is activated,
    // close other menu items on the same level
    $button.parent().siblings().removeClass( hoverClass );

    // Open the sub menu of this parent item
    $button.parent().addClass( hoverClass );
  }

  private onMenuToggleChange( e: JQuery.Event ): void {
    const isMenuOpen = $( e.currentTarget ).prop( 'checked' );
    this.$body.toggleClass( 'nav--is-open', isMenuOpen );
    if ( !isMenuOpen ) {
      setTimeout( () => {
        // Close the open submenus in the mobile menu overlay
        this.$mainMenuItems.removeClass( 'hover' );
        this.$mainMenuItems.find('a').removeClass( 'active' );
      }, 300 );
    }
  }
}
