import $ from 'jquery';
import * as anime from 'animejs';
import { BaseComponent } from '../models/DefaultComponent';

export class Blob extends BaseComponent {
  protected element: JQuery;
  protected seedOffset: number;

  private radius = 10;
  private sides;
  private seed;
  private timeline;

  constructor(sides: number, seed: number, seedOffset: number = 0) {
    super();

    this.sides = sides;
    this.seed = seed + seedOffset;
    this.seedOffset = seedOffset;

    this.bindEvents();
    this.render();
  }

  public generateSvg() {
    const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg' );
    const polygon = document.createElementNS('http://www.w3.org/2000/svg', 'polygon' );

    svg.setAttribute( 'viewBox', '0 0 ' + 2 * this.radius + ' ' + 2 * this.radius );
    svg.setAttribute( 'fill', 'currentColor' );
    polygon.setAttribute( 'points', this.generatePoints( true ) );
    svg.appendChild( polygon );

    this.timeline = anime({
      autoplay: false,
      duration: 1000,
      easing: 'linear',
      offset: 0,
      points: this.generatePoints(),
      targets: polygon,
    });

    return svg;
  }

  public render() {
    const $svg = $( this.generateSvg() );

    if ( this.element ) {
      this.element.replaceWith( $svg );
    }

    this.element = $svg;
  }

  public getRatio(seed: number, i: number): number {
    const pow = Math.pow( seed, i );
    return ( 4 + 6 * this.getMagicDigit( pow ) / 9 ) / 10;
  }

  public setSeed(seed: number) {
    // const seeds = [17, 37, 65, 72, 91, 123, 245, 313, 381, 379];
    // this.seed = seeds[seed - 1];
    this.seed = seed + this.seedOffset;
  }

  public getMagicDigit( n ) {
    let sum = 0;

    while ( n > 0 || sum > 9 ) {
      if ( n === 0 ) {
        n = sum;
        sum = 0;
      }
      sum += n % 10;
      n = Math.floor(n / 10 );
    }
    return sum;
  }

  public setComplexity( complexity ) {
    this.timeline.seek( complexity * 1000 );
  }

  public setSides( sides ) {
    this.sides = sides;
  }

  public generatePoints( random: boolean = false ): string {
    const points = [];

    for (let i = 1; i <= this.sides; i++) {
      // generate a regular polygon
      // we add pi/2 to the angle to have the tip of polygons with odd number of edges pointing upwards
      const angle = 2 * Math.PI * i / this.sides - Math.PI / 2;
      const x = this.radius * Math.cos(angle);
      const y = this.radius * Math.sin(angle);

      // default ratio is 0.7 because the random one varies between 0.4 and 1
      let ratio = 0.7;

      if ( random ) {
        // apply a "random" ratio to the coordinates to create an irregular shape
        ratio = this.getRatio(this.seed, i);
      }

      points.push(x * ratio + this.radius);
      points.push(y * ratio + this.radius);
    }

    return points.join(' ');
  }

  public getSvg(): JQuery {
    return this.element;
  }

  public getSeed(): number {
    return this.seed;
  }

  public bindEvents(): void {

  }

  public destroy(): void {

  }
}
