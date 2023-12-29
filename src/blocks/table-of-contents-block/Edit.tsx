import { useBlockProps } from "@wordpress/block-editor";
import { BlockEditProps } from "@wordpress/blocks";
import { TextControl, SelectControl } from "@wordpress/components";
import "./editor.scss";
import { ListStyle, TableOfContentsBlockAttributes } from "./types";

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */
export default function Edit({
	attributes,
	setAttributes,
}: BlockEditProps<TableOfContentsBlockAttributes>) {
	const MIN_HEADING_LEVEL = 2;
	const MAX_HEADING_LEVEL = 6;
	const props = useBlockProps();
	const { minHeadingLevel, maxHeadingLevel, title, listStyle } = attributes;
	const listStyleOptions = Object.values(ListStyle).map((style) => ({
		label: style,
		value: style,
		disabled: style === listStyle,
	}));
	const onMinHeadingLevelChanged = (minHeadingLevel: string) => {
		const level = minHeadingLevel
			? parseInt(minHeadingLevel)
			: MIN_HEADING_LEVEL;

		setAttributes({
			minHeadingLevel: Math.min(
				Math.max(level, MIN_HEADING_LEVEL),
				maxHeadingLevel,
			),
		});
	};
	const onMaxHeadingLevelChanged = (maxHeadingLevel: string) => {
		const level = maxHeadingLevel
			? parseInt(maxHeadingLevel)
			: MAX_HEADING_LEVEL;

		setAttributes({
			maxHeadingLevel: Math.min(
				Math.max(level, minHeadingLevel),
				MAX_HEADING_LEVEL,
			),
		});
	};
	const onTitleChanged = (title: string) => {
		setAttributes({ title });
	};
	const onListStyleChanged = (listStyle: ListStyle) => {
		setAttributes({ listStyle });
	};

	return (
		<div {...props}>
			<p className="title">Open Table of Contents / Table of Contents</p>

			<TextControl label="Title" value={title} onChange={onTitleChanged} />
			<TextControl
				label="Min. Heading Level"
				type="number"
				value={minHeadingLevel}
				onChange={onMinHeadingLevelChanged}
			/>
			<TextControl
				label="Max. Heading Level"
				type="number"
				value={maxHeadingLevel}
				onChange={onMaxHeadingLevelChanged}
			/>
			<SelectControl
				label="List Style"
				options={listStyleOptions}
				value={listStyle}
				onChange={onListStyleChanged}
			/>
		</div>
	);
}
