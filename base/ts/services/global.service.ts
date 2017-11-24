import * as Rx from 'rx-dom';

interface ExtendedWindow extends Window {
  wp?: any;
}

export class GlobalService {

  public static onCustomizerChange(): Rx.Observable<JQuery> {
    const exWindow: ExtendedWindow = window;

    return Rx.Observable.create( ( observer ) => {
      if ( exWindow.wp && exWindow.wp.customize ) {
        exWindow.wp.customize.selectiveRefresh.bind( 'partial-content-rendered', (placement) => {
          observer.onNext($(placement.container));
        });
      }
    });
  }

  public static onReady(): Rx.Observable<UIEvent> {
    return Rx.DOM.ready();
  }

}
