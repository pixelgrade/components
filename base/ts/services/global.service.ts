import * as Rx from 'rx-dom';

export class GlobalService {

  public static onReady(): Rx.Observable<UIEvent> {
    return Rx.DOM.ready();
  }

}
