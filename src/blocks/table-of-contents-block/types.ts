export enum ListStyle {
	None = "none",
	Decimal = "decimal",
	DecimalLeadingZero = "decimal-leading-zero",
	LowerRoman = "lower-roman",
	UpperRoman = "upper-roman",
	LowerAlpha = "lower-alpha",
	UpperAlpha = "upper-alpha",
	Circle = "circle",
	Disc = "disc",
	Square = "square",
}
export interface TableOfContentsBlockAttributes {
	title: string;
	minHeadingLevel: number;
	maxHeadingLevel: number;
	listStyle: ListStyle;
}
