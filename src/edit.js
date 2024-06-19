/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { useSelect } from '@wordpress/data';
import { store as coreDataStore } from '@wordpress/core-data';
import { SelectControl } from '@wordpress/components';
import { useState } from '@wordpress/element';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */
export default function Edit({ setAttributes, attributes }) {


	const { postType } = attributes;
	const defaultPostType = postType && postType.length > 0 ? postType : 'book';
	const [ selectedPostType, setSelectedPostType ] = useState( defaultPostType );

	const posts = useSelect(
		select =>
			select( coreDataStore ).getEntityRecords( 'postType', selectedPostType ),
		[ selectedPostType ]
	);

	const postTypes = window.myReadingListData.postTypes;

	if ( ! posts ) {
		return (
			<div {...useBlockProps()}>
				<p>{__('No posts found', 'my-reading-list')}</p>
			</div>
		);
	}
	return (
		<div {...useBlockProps()}>
			<InspectorControls>
				<SelectControl
					label="Post Type"
					value={ selectedPostType }
					options={ postTypes }
					onChange={ ( newPostType ) => {
						setSelectedPostType( newPostType );
						setAttributes( { postType: newPostType } );
					} }
				/>
			</InspectorControls>

			{posts.map((post) => (
				<div>
					<h2>{post.title.rendered}</h2>
					<img src={post.featured_image_src}/>
					<div dangerouslySetInnerHTML={{__html: post.content.rendered}}></div>
				</div>
			))}
		</div>
	);
}
