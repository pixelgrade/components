import { Observable, fromEvent } from 'rxjs';

export interface ExtendedWindow extends Window {
  wp?: any;
  safari?: any;
}

export class GlobalService {

  public static onCustomizerRender(): Observable<JQuery> {
    const exWindow: ExtendedWindow = window;

    return Observable.create( ( observer ) => {
      if ( exWindow.wp && exWindow.wp.customize && exWindow.wp.customize.selectiveRefresh ) {
        exWindow.wp.customize.selectiveRefresh.bind( 'partial-content-rendered', (placement) => {
          observer.onNext($(placement.container));
        });
      }
    });
  }

  public static onCustomizerChange(): Observable<JQuery> {
    const exWindow: ExtendedWindow = window;

    return Observable.create( ( observer ) => {
      if ( exWindow.wp && exWindow.wp.customize ) {
        exWindow.wp.customize.bind( 'change', ( setting ) => {
          observer.onNext( setting );
        });
      }
    });
  }

  public static onReady(): Observable<Event> {
    return fromEvent( window.document, 'ready' );
  }

}
