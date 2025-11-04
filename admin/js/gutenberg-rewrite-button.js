/**
 * AISEO Gutenberg Rewrite Button
 * Adds a rewrite button with dropdown options to all blocks
 */

(function (wp) {
    const { registerPlugin } = wp.plugins;
    const { PluginBlockSettingsMenuItem } = wp.editPost;
    const { Fragment } = wp.element;
    const { BlockControls } = wp.blockEditor;
    const { ToolbarGroup, ToolbarDropdownMenu, Spinner } = wp.components;
    const { createHigherOrderComponent } = wp.compose;
    const { addFilter } = wp.hooks;
    const { select, dispatch } = wp.data;
    const { __ } = wp.i18n;

    // Rewrite modes with icons (minimal changes)
    const rewriteModes = [
        {
            title: __('Fix Grammar', 'aiseo'),
            icon: 'yes',
            mode: 'grammar',
            description: 'Fix grammar and spelling only'
        },
        {
            title: __('Improve Clarity', 'aiseo'),
            icon: 'visibility',
            mode: 'clarity',
            description: 'Make it clearer without major changes'
        },
        {
            title: __('Shorten', 'aiseo'),
            icon: 'editor-contract',
            mode: 'concise',
            description: 'Remove unnecessary words'
        },
        {
            title: __('Professional Tone', 'aiseo'),
            icon: 'businessman',
            mode: 'professional',
            description: 'Adjust tone to be more professional'
        },
        {
            title: __('Casual Tone', 'aiseo'),
            icon: 'smiley',
            mode: 'casual',
            description: 'Adjust tone to be more casual'
        },
        {
            title: __('Simplify', 'aiseo'),
            icon: 'lightbulb',
            mode: 'simplify',
            description: 'Use simpler words'
        }
    ];

    // State to track rewriting blocks
    const rewritingBlocks = new Set();

    /**
     * Rewrite block content
     */
    function rewriteBlockContent(clientId, mode) {
        const block = select('core/block-editor').getBlock(clientId);
        
        if (!block) {
            console.error('Block not found');
            return;
        }

        // Get text content from block
        let content = '';
        if (block.attributes.content) {
            content = block.attributes.content;
        } else if (block.attributes.text) {
            content = block.attributes.text;
        } else if (block.attributes.value) {
            content = block.attributes.value;
        } else {
            content = block.innerBlocks.map(b => b.attributes.content || '').join(' ');
        }

        // Strip HTML tags
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = content;
        const textContent = tempDiv.textContent || tempDiv.innerText || '';

        if (!textContent.trim()) {
            alert(__('No content to rewrite in this block', 'aiseo'));
            return;
        }

        // Mark block as rewriting
        rewritingBlocks.add(clientId);

        // Show loading state
        const originalContent = content;
        dispatch('core/block-editor').updateBlockAttributes(clientId, {
            content: '<p><em>Rewriting content...</em></p>'
        });

        // Call AJAX to rewrite
        jQuery.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'aiseo_rewrite_content',
                nonce: aiseGutenbergData.nonce,
                content: textContent,
                mode: mode,
                minimal: true,  // Flag for minimal changes
                preserve_meaning: true  // Keep original meaning
            },
            success: function (response) {
                rewritingBlocks.delete(clientId);
                
                if (response.success && response.data) {
                    // Update block with rewritten content
                    dispatch('core/block-editor').updateBlockAttributes(clientId, {
                        content: response.data
                    });
                } else {
                    // Restore original content on error
                    dispatch('core/block-editor').updateBlockAttributes(clientId, {
                        content: originalContent
                    });
                    alert(__('Error: ', 'aiseo') + (response.data || 'Failed to rewrite content'));
                }
            },
            error: function (xhr, status, error) {
                rewritingBlocks.delete(clientId);
                
                // Restore original content
                dispatch('core/block-editor').updateBlockAttributes(clientId, {
                    content: originalContent
                });
                
                console.error('Rewrite error:', error);
                alert(__('Connection error. Please try again.', 'aiseo'));
            }
        });
    }

    /**
     * Add rewrite button to block toolbar
     */
    const withRewriteButton = createHigherOrderComponent((BlockEdit) => {
        return (props) => {
            const { name, clientId, isSelected } = props;

            // Only show for text-based blocks
            const supportedBlocks = [
                'core/paragraph',
                'core/heading',
                'core/list',
                'core/quote',
                'core/verse',
                'core/code'
            ];

            const showButton = isSelected && supportedBlocks.includes(name);

            return (
                <Fragment>
                    <BlockEdit {...props} />
                    {showButton && (
                        <BlockControls group="block">
                            <ToolbarGroup>
                                <ToolbarDropdownMenu
                                    icon="edit"
                                    label={__('AI Rewrite', 'aiseo')}
                                    controls={rewriteModes.map(mode => ({
                                        title: mode.title,
                                        icon: mode.icon,
                                        onClick: () => rewriteBlockContent(clientId, mode.mode)
                                    }))}
                                />
                            </ToolbarGroup>
                        </BlockControls>
                    )}
                </Fragment>
            );
        };
    }, 'withRewriteButton');

    // Register the filter
    addFilter(
        'editor.BlockEdit',
        'aiseo/rewrite-button',
        withRewriteButton
    );

})(window.wp);
