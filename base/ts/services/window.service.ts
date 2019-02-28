import $ from 'jquery';

import { Observable, fromEvent } from 'rxjs';

export class WindowService {

  private static $window: JQuery<Window> = $( window );

  public static onLoad(): Observable<Event> {
    return fromEvent( window, 'load' );
  }

  public static onResize(): Observable<Event> {
    return fromEvent( window, 'resize');
  }

  public static onScroll(): Observable<Event> {
    return fromEvent( window, 'scroll');
  }

  public static getWindow(): JQuery<Window> {
    return WindowService.$window;
  }

  public static getScrollY() {
    return (window.pageYOffset || document.documentElement.scrollTop)  - (document.documentElement.clientTop || 0);
  }

  public static getWidth(): number {
    return WindowService.$window.width();
  }

  public static getHeight(): number {
    return WindowService.$window.height();
  }

  public static getWindowEl(): Window {
    return WindowService.$window[ 0 ];
  }

  public static getOrientation(): string {
    return WindowService.getWidth() > WindowService.getHeight() ? 'landscape' : 'portrait';
  }
}
