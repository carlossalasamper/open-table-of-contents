import { useBlockProps } from "@wordpress/block-editor";

/**
 * The save function defines the way in which the different attributes should
 * be combined into the final markup, which is then serialized by the block
 * editor into `post_content`.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#save
 *
 * @return {Element} Element to render.
 */
export default function Save() {
	return (
		<div {...useBlockProps.save()} className="open-table-of-contents">
			⚠️ Open Table of Contents rendered on the client side
		</div>
	);
}
