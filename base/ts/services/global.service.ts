import { Observable, fromEvent } from 'rxjs';

export interface ExtendedWindow extends Window {
  wp?: any;
  safari?: any;
}

export class GlobalService {

  public static onCustomizerRender(): Observable<JQuery> {
    const exWindow: ExtendedWindow = window;

    return Observable.create( ( observer ) => {
      const callback = observer.next.bind( observer );
      if ( exWindow.wp && exWindow.wp.customize && exWindow.wp.customize.selectiveRefresh ) {
        exWindow.wp.customize.selectiveRefresh.bind( 'partial-content-rendered', ( value ) => {
          callback( value );
        } );
      }
    });
  }

  public static onCustomizerChange(): Observable<JQuery> {
    const exWindow: ExtendedWindow = window;

    return Observable.create( ( observer ) => {
      const callback = observer.next.bind( observer );
      if ( exWindow.wp && exWindow.wp.customize ) {
        exWindow.wp.customize.bind( 'change', ( value ) => {
          callback( value );
        } );
      }
    });
  }

  public static onReady(): Observable<Event> {
    return fromEvent( document, 'DOMContentLoaded' );
  }

}
