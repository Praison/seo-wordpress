/**
 * AISEO Gutenberg SEO Score Improver
 * Adds "Improve SEO Score" button to post sidebar
 */

(function (wp) {
    const { registerPlugin } = wp.plugins;
    const { PluginDocumentSettingPanel } = wp.editPost;
    const { Button, Spinner, Notice } = wp.components;
    const { Fragment, useState, useEffect } = wp.element;
    const { useSelect, useDispatch } = wp.data;
    const { __ } = wp.i18n;

    /**
     * SEO Score Improver Panel
     */
    const SEOScoreImprover = () => {
        const [isImproving, setIsImproving] = useState(false);
        const [seoScore, setSeoScore] = useState(null);
        const [analysis, setAnalysis] = useState(null);
        const [message, setMessage] = useState('');
        const [messageType, setMessageType] = useState('info');

        // Get current post data
        const { postId, postContent, postTitle, blocks } = useSelect((select) => {
            const editor = select('core/editor');
            const blockEditor = select('core/block-editor');
            return {
                postId: editor.getCurrentPostId(),
                postContent: editor.getEditedPostContent(),
                postTitle: editor.getEditedPostAttribute('title'),
                blocks: blockEditor.getBlocks()
            };
        });

        const { editPost } = useDispatch('core/editor');
        const { updateBlockAttributes, replaceBlocks } = useDispatch('core/block-editor');

        /**
         * Get SEO Analysis
         */
        const getSEOAnalysis = () => {
            return new Promise((resolve, reject) => {
                jQuery.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'aiseo_analyze_content',
                        nonce: aiseoSEOImprover.nonce,
                        post_id: postId
                    },
                    success: function (response) {
                        if (response.success && response.data) {
                            resolve(response.data);
                        } else {
                            reject(response.data || 'Failed to analyze content');
                        }
                    },
                    error: function (xhr, status, error) {
                        reject('Connection error: ' + error);
                    }
                });
            });
        };

        /**
         * Improve SEO Score
         */
        const improveSEOScore = async () => {
            setIsImproving(true);
            setMessage('Analyzing content...');
            setMessageType('info');

            try {
                // Step 1: Get SEO Analysis
                const analysisData = await getSEOAnalysis();
                setAnalysis(analysisData);
                setSeoScore(analysisData.overall_score || 0);

                if (analysisData.overall_score >= 80) {
                    setMessage('SEO score is already good (80+). No improvements needed.');
                    setMessageType('success');
                    setIsImproving(false);
                    return;
                }

                // Step 2: Improve SEO Title & Description
                setMessage('Improving SEO title and description...');
                await improveTitleAndDescription(analysisData);

                // Step 3: Improve Content
                setMessage('Improving content based on SEO analysis...');
                await improveContent(analysisData);

                // Step 4: Re-analyze to show new score
                setMessage('Re-analyzing content...');
                const newAnalysis = await getSEOAnalysis();
                setSeoScore(newAnalysis.overall_score || 0);

                setMessage(`SEO Score improved from ${analysisData.overall_score}/100 to ${newAnalysis.overall_score}/100!`);
                setMessageType('success');

            } catch (error) {
                console.error('SEO Improvement Error:', error);
                setMessage('Error: ' + error);
                setMessageType('error');
            } finally {
                setIsImproving(false);
            }
        };

        /**
         * Improve Title and Description
         */
        const improveTitleAndDescription = (analysisData) => {
            return new Promise((resolve, reject) => {
                jQuery.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'aiseo_improve_seo_meta',
                        nonce: aiseoSEOImprover.nonce,
                        post_id: postId,
                        current_title: postTitle,
                        analysis: JSON.stringify(analysisData)
                    },
                    success: function (response) {
                        if (response.success && response.data) {
                            // Update title in editor
                            if (response.data.title) {
                                editPost({ title: response.data.title });
                            }
                            resolve(response.data);
                        } else {
                            reject(response.data || 'Failed to improve meta');
                        }
                    },
                    error: function (xhr, status, error) {
                        reject('Connection error: ' + error);
                    }
                });
            });
        };

        /**
         * Improve Content (Block by Block)
         */
        const improveContent = (analysisData) => {
            return new Promise(async (resolve, reject) => {
                try {
                    let improvedCount = 0;

                    // Process each block
                    for (const block of blocks) {
                        if (shouldImproveBlock(block)) {
                            await improveBlock(block, analysisData);
                            improvedCount++;
                        }
                    }

                    resolve({ improved: improvedCount });
                } catch (error) {
                    reject(error);
                }
            });
        };

        /**
         * Check if block should be improved
         */
        const shouldImproveBlock = (block) => {
            const improvableBlocks = [
                'core/paragraph',
                'core/heading',
                'core/list',
                'core/quote'
            ];
            return improvableBlocks.includes(block.name) && 
                   (block.attributes.content || block.attributes.values);
        };

        /**
         * Improve individual block
         */
        const improveBlock = (block, analysisData) => {
            return new Promise((resolve, reject) => {
                const content = block.attributes.content || '';
                
                // Strip HTML to get text
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = content;
                const textContent = tempDiv.textContent || tempDiv.innerText || '';

                if (!textContent.trim()) {
                    resolve();
                    return;
                }

                jQuery.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'aiseo_improve_content_block',
                        nonce: aiseoSEOImprover.nonce,
                        content: textContent,
                        block_type: block.name,
                        analysis: JSON.stringify(analysisData),
                        preserve_structure: true,
                        minimal_changes: true
                    },
                    success: function (response) {
                        if (response.success && response.data) {
                            // Update block content
                            updateBlockAttributes(block.clientId, {
                                content: response.data
                            });
                        }
                        resolve();
                    },
                    error: function () {
                        // Don't fail entire process if one block fails
                        resolve();
                    }
                });
            });
        };

        return (
            <PluginDocumentSettingPanel
                name="aiseo-seo-improver"
                title={__('SEO Score Improver', 'aiseo')}
                icon="chart-line"
            >
                <div style={{ padding: '10px 0' }}>
                    {seoScore !== null && (
                        <div style={{ 
                            marginBottom: '15px', 
                            padding: '10px', 
                            background: seoScore >= 80 ? '#d4edda' : seoScore >= 50 ? '#fff3cd' : '#f8d7da',
                            borderRadius: '4px',
                            textAlign: 'center'
                        }}>
                            <strong>Current SEO Score: {seoScore}/100</strong>
                        </div>
                    )}

                    {message && (
                        <Notice
                            status={messageType}
                            isDismissible={false}
                            style={{ marginBottom: '10px' }}
                        >
                            {message}
                        </Notice>
                    )}

                    <Button
                        isPrimary
                        isLarge
                        disabled={isImproving}
                        onClick={improveSEOScore}
                        style={{ width: '100%' }}
                    >
                        {isImproving ? (
                            <Fragment>
                                <Spinner />
                                {__('Improving...', 'aiseo')}
                            </Fragment>
                        ) : (
                            __('Improve SEO Score', 'aiseo')
                        )}
                    </Button>

                    <p style={{ 
                        fontSize: '12px', 
                        color: '#666', 
                        marginTop: '10px',
                        lineHeight: '1.4'
                    }}>
                        This will automatically improve your SEO title, description, and content based on SEO analysis while preserving the structure.
                    </p>
                </div>
            </PluginDocumentSettingPanel>
        );
    };

    // Register the plugin
    registerPlugin('aiseo-seo-improver', {
        render: SEOScoreImprover,
        icon: 'chart-line'
    });

})(window.wp);
