jQuery(function ( $ ) {
    var isAdding = false;

    function clearUI() {
        $( '#emwi-urls' ).val( '' );
        $( '#emwi-hidden' ).hide();
        $( '#emwi-error' ).text( '' );
        $( '#emwi-width' ).val( '' );
        $( '#emwi-height' ).val( '' );
        $( '#emwi-mime-type' ).val( '' );
    }

    $( 'body' ).on( 'click', '#emwi-clear', function ( e ) {
        clearUI();
        $( '#emwi-result-log' ).empty(); // 手动清空时，连带清空日志
    });

    $( 'body' ).on( 'click', '#emwi-show', function ( e ) {
        $( '#emwi-media-new-panel' ).show();
        e.preventDefault();
    });

    $( 'body' ).on( 'click', '#emwi-add', function ( e ) {
        if ( isAdding ) return;
        
        var $urlsInput = $( '#emwi-urls' );
        if ( ! $urlsInput.val().trim() ) return;

        // 【增加代码】：每次点击添加时，清空上一次的旧日志
        $( '#emwi-result-log' ).empty(); 
        isProcessing(true);

        var postData = {
            'action': 'eezz_url2wp_add_media',
            'nonce':  eezzUrl2Wp.nonce,
            'urls':   $urlsInput.val(),
            'width':  $( '#emwi-width' ).val(),
            'height': $( '#emwi-height' ).val(),
            'mime-type': $( '#emwi-mime-type' ).val()
        };

        // 【增加代码】：在清空 textarea 前，记录本次提交的所有 URL 用于比对
        var submittedUrls = postData.urls.split('\n').map(function(u){ return u.trim(); }).filter(function(u){ return u; });

        wp.media.post( 'eezz_url2wp_add_media', postData )
            .done(function ( response ) {
                var frame = wp.media.frame || wp.media.library;
                if ( frame ) {
                    if ( frame.content && typeof frame.content.mode === 'function' ) {
                        frame.content.mode( 'browse' );
                    }
                    var library = frame.state().get( 'library' ) || frame.library;
                    response.attachments.forEach( function ( elem ) {
                        var attachment = wp.media.model.Attachment.create( elem );
                        attachment.fetch(); 
                        library.add( attachment ? [ attachment ] : [] );
                        if ( wp.media.frame._state != 'library' ) {
                            var selection = frame.state().get( 'selection' );
                            if ( selection ) {
                                selection.add( attachment );
                            }
                        }
                    } );
                }

                // ==========================================
                // 【增加代码】：生成 ✅ ❌ 结果日志
                // ==========================================
                // 获取后端返回的失败 URL 数组
                var failedUrls = response['urls'] ? response['urls'].split('\n').map(function(u){ return u.trim(); }) : [];
                
                var logHtml = '<div style="margin-top: 15px; padding: 10px; background: #f6f7f7; border-left: 4px solid #72aee6; border-radius: 4px; max-height: 200px; overflow-y: auto;">';
                logHtml += '<strong style="display:block; margin-bottom:8px; color:#1d2327;">处理结果：</strong>';
                
                // 遍历原始提交的 URL，如果在失败数组里就是 ❌，不在就是 ✅
                submittedUrls.forEach(function(url) {
                    if (failedUrls.indexOf(url) !== -1) {
                        logHtml += '<div style="color: #d63638; margin-bottom: 4px; word-break: break-all;">❌ ' + url + '</div>';
                    } else {
                        logHtml += '<div style="color: #00a32a; margin-bottom: 4px; word-break: break-all;">✅ ' + url + '</div>';
                    }
                });
                logHtml += '</div>';
                
                // 将生成的 HTML 注入到容器中
                $('#emwi-result-log').html(logHtml);
                // ==========================================

                if ( response['error'] ) {
                    $( '#emwi-error' ).text( response['error'] );
                    $( '#emwi-width' ).val( response['width'] );
                    $( '#emwi-height' ).val( response['height'] );
                    $( '#emwi-mime-type' ).val( response['mime-type'] );
                    $( '#emwi-hidden' ).show(); 
                } else {
                    clearUI(); // 成功后仅清空输入框，不清空刚才渲染的日志
                    $( '#emwi-hidden' ).hide();
                    
                    // 【修改代码】：注释掉自动隐藏逻辑。为了让用户看清楚 ✅ 日志，面板不再秒关。
                    // if ( $( '#emwi-show' ).length ) {
                    //     $( '#emwi-media-new-panel' ).hide();
                    // }
                }

                $( '#emwi-urls' ).val( response['urls'] ); 
            })
            .fail(function () {
                $( '#emwi-error' ).text( eezzUrl2Wp.strings.error );
                $( '#emwi-hidden' ).show();
            })
            .always(function () {
                isProcessing(false);
            });

        e.preventDefault();
    });

    $( 'body' ).on( 'click', '#emwi-cancel', function ( e ) {
        clearUI();
        $( '#emwi-result-log' ).empty(); // 点击取消时清空日志
        $( '#emwi-media-new-panel' ).hide();
        isProcessing(false);
        e.preventDefault();
    });

    function isProcessing( state ) {
        isAdding = state;
        $( '#emwi-add' ).prop( 'disabled', state );
        $( '#emwi-buttons-row .spinner' ).css( 'visibility', state ? 'visible' : 'hidden' );
        if ( state ) {
            $( '#emwi-buttons-row .spinner' ).addClass( 'is-active' );
        } else {
            $( '#emwi-buttons-row .spinner' ).removeClass( 'is-active' );
        }
    }
});