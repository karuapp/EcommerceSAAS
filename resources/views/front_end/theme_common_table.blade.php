<section class="tab-vid-section padding-top padding-bottom">
            <div class="container">
                <div class="tabs-wrapper">
                    <div class="blog-head-row tab-nav d-flex justify-content-between">
                        <div class="blog-col-left ">
                            <ul class="d-flex tabs">
                                <li class="tab-link on-tab-click active" data-tab="product_description_tab"><a
                                        href="javascript:;">{{ __('Description') }}</a>
                                </li>
                              
                                <li class="tab-link on-tab-click" data-tab="product_qus_ans_tab"><a
                                        href="javascript:;">{{ __('Question & Answer') }}</a>
                                </li>
                                @if ($product->product_attribute != '')
                                    <li class="tab-link on-tab-click" data-tab="product_add_info_tab"><a
                                            href="javascript:;">{{ __('Additional Information') }}</a>
                                    </li>
                                @endif
                                @if ($product->preview_content != '')
                                    <li class="tab-link on-tab-click" data-tab="product_video_tab"><a
                                            href="javascript:;">{{ __('Video') }}</a>
                                    </li>
                                @endif
                                @include('front_end.hooks.product_tab')

                            </ul>
                        </div>
                    </div>
                    <div class="tabs-container">
                    <div id="product_description_tab" class="tab-content active">
                            <div class="row row-gap">
                                <div class="col-md-6 col-12">
                                    <div class="pro-descrip-contente-left">
                                        <div class="badge">
                                            {{ __('About Product') }}
                                        </div>
                                        <div class="section-title">
                                            <h3>{{ __('Description') }}:</h3>
                                        </div>
                                        <p>{!! $product->detail !!} </p>
                                    </div>
                                </div>
                                <div class="col-md-6 col-12">
                                    <div class="pro-descrip-contente-right">
                                        <div class="badge">
                                            About Product
                                        </div>
                                        <div class="section-title">
                                            <h3>{{ __('Specification') }} :</h3>
                                        </div>
                                        <p>{!! $product->specification !!}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        

                        <div id="product_qus_ans_tab" class="tab-content ">
                            <div class="queary-div">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h4>{{ __('Have doubts regarding this product?') }}</h4>
                                    <a href="javascript:void(0)" class="btn btn-sm btn-primary Question"
                                        @if (auth('customers')->check()) data-ajax-popup="true" @else data-ajax-popup="false" @endif
                                        data-size="xs" data-title="Post your question"
                                        data-url="{{ route('question', [$slug, $product->id]) }} "
                                        data-toggle="tooltip">
                                        <i class="ti ti-plus"></i>
                                        <span class="lbl">{{ __('Post Your Question') }}</span>
                                    </a>
                                </div>
                                <div class="qna">
                                    <br>
                                    <ul>
                                        @foreach ($question->take(4) as $que)
                                            <li>
                                                <div class="quetion">
                                                    <span class="icon que">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="305"
                                                            height="266" viewBox="0 0 305 266" fill="none"
                                                            class="__web-inspector-hide-shortcut__">
                                                            <path
                                                                d="M152.4 256.4C222.8 256.4 283.6 216.2 300.1 158.6C303 148.8 304.4 138.6 304.4 128.4C304.4 57.7999 236.2 0.399902 152.4 0.399902C68.6004 0.399902 0.400391 57.7999 0.400391 128.4C0.600391 154.8 10.0004 180.3 27.0004 200.5C28.8004 202.7 29.3004 205.7 28.3004 208.4L6.70039 265.4L68.2004 238.4C70.4004 237.4 72.9004 237.5 75.0004 238.6C95.8004 248.9 118.4 254.9 141.5 256.1C145.2 256.3 148.8 256.4 152.4 256.4ZM104.4 120.4C104.4 85.0999 125.9 56.3999 152.4 56.3999C178.9 56.3999 200.4 85.0999 200.4 120.4C200.5 134.5 196.8 148.5 189.7 160.6L204.5 169.5C207 170.9 208.5 173.6 208.5 176.5C208.5 179.4 206.9 182 204.3 183.4C201.7 184.8 198.7 184.7 196.2 183.2L179.4 173.1C172.1 180.1 162.4 184.1 152.3 184.3C125.9 184.4 104.4 155.7 104.4 120.4Z"
                                                                fill="black" />
                                                            <path
                                                                d="M164.9 164.4L156.3 159.2C152.6 156.9 151.4 152 153.7 148.3C156 144.6 160.8 143.3 164.6 145.5L176 152.4C181.6 142.7 184.6 131.6 184.4 120.4C184.4 94.3999 169.7 72.3999 152.4 72.3999C135.1 72.3999 120.4 94.3999 120.4 120.4C120.4 146.4 135.1 168.4 152.4 168.4C156.8 168.3 161.2 166.9 164.9 164.4Z"
                                                                fill="black" />
                                                        </svg>
                                                    </span>
                                                    <div class="text">
                                                        <p>
                                                            {{ $que->question }}
                                                        </p>
                                                        <span class="user">{{ __($que->users->first_name ?? '') }}</span>
                                                    </div>
                                                </div>
                                                <div class="answer">
                                                    <span class="icon ans">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="304"
                                                            height="273" viewBox="0 0 304 273" fill="none">
                                                            <path
                                                                d="M304 127.3C304 126.8 304 126.2 304 125.7C304 125.2 304 124.7 303.9 124.2C301.4 55.5002 234.2 0.200195 152 0.200195C68.5 0.200195 0.6 57.1002 0 127.3C0 127.7 0 128 0 128.4C0.2 154.7 9.6 180.2 26.6 200.4C27.2 201.1 27.6 201.9 27.9 202.7C39.6 216.7 54.6 228.5 71.9 237.6C72.8 237.7 73.7 238 74.6 238.4C95.4 248.7 118 254.7 141.1 255.9C144.8 256.2 148.4 256.3 152 256.3C222.4 256.3 283.2 216.1 299.7 158.5C301.2 153.4 302.3 148.3 303 143.1C303.1 142.4 303.2 141.7 303.3 141C303.4 140.5 303.4 140.1 303.5 139.6C303.6 139 303.6 138.4 303.7 137.9C303.7 137.3 303.8 136.7 303.8 136.1C303.8 135.9 303.8 135.8 303.8 135.6C303.8 135.1 303.9 134.5 303.9 134C303.9 133.3 304 132.6 304 132C304 131.6 304 131.2 304 130.8C304 130.4 304 130 304 129.7C304 129.4 304 129.2 304 128.9V128.5C304 128.1 304 127.7 304 127.3ZM204 183.3C201.5 184.7 198.4 184.6 195.9 183.1L193.7 181.8L199.5 198.2C201 202.4 198.8 206.9 194.7 208.4C190.5 209.9 186 207.7 184.5 203.6L174.9 176.6C168.3 181.4 160.3 184.1 152.1 184.3C143.9 184.3 136.1 181.5 129.3 176.6L119.7 203.6C118.2 207.8 113.6 209.9 109.5 208.4C105.3 206.9 103.2 202.3 104.7 198.2L117 163.7C109.1 152.3 104.2 137 104.2 120.3C104.2 85.0002 125.7 56.3002 152.2 56.3002C178.7 56.3002 200.2 85.0002 200.2 120.3C200.4 134.4 196.6 148.3 189.5 160.5L204.3 169.4C206.8 170.9 208.3 173.5 208.3 176.4C208.1 179.3 206.5 181.9 204 183.3Z"
                                                                fill="black" />
                                                            <path
                                                                d="M304 127.3C304 126.8 304 126.2 304 125.7C304 125.2 304 124.7 303.9 124.2C301.2 61.1002 243.4 8.7002 169.1 1.7002C168.8 2.7002 168.3 3.60019 168 4.50019C167.3 6.40019 166.6 8.20019 165.8 10.1002C165 12.0002 164.1 13.9002 163.2 15.8002C162.3 17.7002 161.4 19.4002 160.5 21.2002C159.5 23.0002 158.5 24.8002 157.5 26.5002C156.5 28.3002 155.4 30.0002 154.3 31.7002C153.2 33.4002 152 35.1002 150.8 36.7002C149.6 38.3002 148.4 40.0002 147.1 41.7002C145.8 43.3002 144.5 44.8002 143.2 46.4002C141.9 47.9002 140.5 49.5002 139.1 51.1002C137.7 52.6002 136.2 54.0002 134.8 55.5002C133.3 56.9002 131.8 58.4002 130.3 59.8002C128.8 61.2002 127.2 62.6002 125.5 63.9002C123.9 65.2002 122.3 66.6002 120.6 67.9002C118.9 69.2002 117.2 70.4002 115.4 71.7002C113.7 72.9002 112 74.1002 110.2 75.3002C108.4 76.5002 106.5 77.6002 104.6 78.7002C102.7 79.8002 101 80.9002 99.2 81.9002C97.3 82.9002 95.2 84.0002 93.2 85.0002C91.3 85.9002 89.5 86.9002 87.6 87.8002C85.5 88.8002 83.3 89.6002 81.2 90.5002C79.3 91.3002 77.4 92.1002 75.5 92.9002C73.3 93.7002 70.9 94.5002 68.6 95.2002C66.7 95.8002 64.7 96.5002 62.8 97.1002C60.4 97.8002 57.9 98.4002 55.4 99.0002C53.5 99.5002 51.6 100 49.6 100.4C47 101 44.3 101.4 41.6 101.9C39.8 102.2 37.9 102.6 36.1 102.9C33.1 103.3 30 103.6 26.9 103.9C25.3 104.1 23.8 104.3 22.2 104.4C17.5 104.7 12.7 104.9 8 104.9C6.2 104.9 4.5 104.9 2.7 104.8C0.999997 112.2 0.1 119.8 0 127.3C0 127.7 0 128 0 128.4V128.8C0 156.3 10.3 181.7 27.9 202.6C39.6 216.6 54.6 228.4 71.9 237.5C95.2 249.7 122.6 256.8 152 256.8C176.6 256.9 201 251.8 223.5 241.8C225.6 240.8 228.1 240.8 230.2 241.8L296.4 272.7L271.6 214.8C270.4 211.9 270.9 208.6 273 206.3C289.5 188.8 299.9 166.7 303 143.1C303.1 142.4 303.2 141.7 303.3 141C303.4 140.5 303.4 140.1 303.5 139.6C303.6 139 303.6 138.4 303.7 137.9C303.7 137.3 303.8 136.7 303.8 136.1C303.8 135.9 303.8 135.8 303.8 135.6C303.8 135.1 303.9 134.5 303.9 134C303.9 133.3 304 132.6 304 132C304 131.6 304 131.2 304 130.8C304 130.4 304 130 304 129.7C304 129.4 304 129.2 304 128.9V128.5C304 128.1 304 127.7 304 127.3ZM119.5 203.5C118 207.7 113.4 209.8 109.3 208.3C105.1 206.8 103 202.2 104.5 198.1L116.8 163.6L144.5 86.1002C145.6 82.9002 148.7 80.8002 152 80.8002C155.3 80.8002 158.4 82.9002 159.5 86.1002L193.7 181.7L199.5 198.1C201 202.3 198.8 206.8 194.7 208.3C190.5 209.8 186 207.6 184.5 203.5L174.9 176.5L172.1 168.8H132L129.2 176.5L119.5 203.5Z"
                                                                fill="black" />
                                                            <path d="M152 112.6L137.6 152.8H166.3L152 112.6Z"
                                                                fill="black" />
                                                        </svg>
                                                    </span>
                                                    <div class="text">
                                                        <p>
                                                            {{ !empty($que->answers) ? $que->answers : 'We will provide the answer to your question shortly!' }}
                                                        </p>
                                                        <span
                                                            class="user">{{ !empty($que->admin->name) ? $que->admin->name : '' }}</span>
                                                    </div>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                    @if ($question->count() >= '4')
                                        <div class="text-center">
                                            <a href="javascript:void(0)" class="load-more-btn btn" data-ajax-popup="true"
                                                data-size="xs" data-title="Questions And Answers"
                                                data-url="{{ route('more_question', [$slug, $product->id]) }} "
                                                data-toggle="tooltip" title="{{ __('Questions And Answers') }}">
                                                {{ __('Load More') }}
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @if ($product->product_attribute != '')
                            <div id="product_add_info_tab" class="tab-content ">
                                <div class="queary-div">

                                        <div class="d-flex justify-content-between align-items-center">
                                            <h4>{{ __('Additional Information about that Product..') }}</h4>
                                        </div>

                                        @foreach (json_decode($product->product_attribute) as $key => $choice_option)
                                            @php
                                                $value = implode(',', $choice_option->values);
                                                $idsArray = explode('|', $value);
                                                $get_datas = \App\Models\ProductAttributeOption::whereIn('id', $idsArray)
                                                    ->get()
                                                    ->pluck('terms')
                                                    ->toArray();

                                                $attribute_id = $choice_option->attribute_id;
                                                $visible_attribute = isset($choice_option->{'visible_attribute_' . $attribute_id}) ? $choice_option->{'visible_attribute_' . $attribute_id} : 0;
                                            @endphp
                                            @if ($visible_attribute == 1)
                                                <div class="row row-gap">
                                                    <div class="col-md-6 col-12">
                                                        <div class="pro-descrip-contente-left">
                                                            <div class="section-title">
                                                                <h6>{{ \App\Models\ProductAttribute::find($choice_option->attribute_id)->name }}
                                                                </h6>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 col-12">
                                                        <div class="pro-descrip-contente-right">
                                                            <div class="badge-wrapper">
                                                                @foreach ($get_datas as $f)
                                                                    <div class="badge">
                                                                        {{ $f }}
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach

                                </div>
                            </div>
                        @endif
                        <div id="product_video_tab" class="tab-content">
                            <div class="video-wrapper">
                                @if ($product->preview_type == 'Video Url')
                                @if (str_contains($product->preview_content, 'youtube') ||
                                str_contains($product->preview_content, 'youtu.be'))
                                @php
                                if (strpos($product->preview_content, 'src') !== false) {
                                preg_match('/src="([^"]+)"/', $product->preview_content, $match);
                                $url = $match[1];
                                $video_url = str_replace('https://www.youtube.com/embed/', '', $url);
                                } elseif (strpos($product->preview_content, 'src') == false && strpos($product->preview_content,
                                'embed') !== false) {
                                $video_url = str_replace('https://www.youtube.com/embed/', '', $product->preview_content);
                                } else {
                                $video_url = str_replace('https://youtu.be/', '',
                                str_replace('https://www.youtube.com/watch?v=', '', $product->preview_content));
                                preg_match('/[\\?\\&]v=([^\\?\\&]+)/', $product->preview_content, $matches);
                                if (count($matches) > 0) {
                                $videoId = $matches[1];
                                $video_url = strtok($videoId, '&');
                                }
                                }
                                @endphp
                                <iframe class="video-card-tag" width="100%" height="100%"
                                    src="{{ 'https://www.youtube.com/embed/' }}{{ $video_url }}" title="YouTube video player"
                                    frameborder="0"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen></iframe>
                                @elseif(str_contains($product->preview_content, 'vimeo'))
                                @php
                                if (strpos($product->preview_content, 'src') !== false) {
                                preg_match('/src="([^"]+)"/', $product->preview_content, $match);
                                $url = $match[1];
                                $video_url = str_replace('https://player.vimeo.com/video/', '', $url);
                                } else {
                                $video_url = str_replace('https://vimeo.com/', '', $product->preview_content);
                                }
                                @endphp
                                <iframe class="video-card-tag" width="100%" height="350"
                                    src="{{ 'https://player.vimeo.com/video/' }}{{ $video_url }}" frameborder="0"
                                    allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>
                                @else
                                @php
                                $video_url = $product->preview_content;
                                @endphp
                                <iframe class="video-card-tag" width="100%" height="100%" src="{{ $video_url }}"
                                    title="Video player" frameborder="0"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen></iframe>
                                @endif
                                @elseif($product->preview_type == 'iFrame')
                                @if (str_contains($product->preview_content, 'youtube') ||
                                str_contains($product->preview_content, 'youtu.be'))
                                @php
                                if (strpos($product->preview_content, 'src') !== false) {
                                preg_match('/src="([^"]+)"/', $product->preview_content, $match);
                                $url = $match[1];
                                $iframe_url = str_replace('https://www.youtube.com/embed/', '', $url);
                                } else {
                                $iframe_url = str_replace('https://youtu.be/', '',
                                str_replace('https://www.youtube.com/watch?v=', '', $product->preview_content));
                                }
                                @endphp
                                <iframe width="100%" height="100%" src="https://www.youtube.com/embed/{{ $iframe_url }}"
                                    title="YouTube video player" frameborder="0"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen></iframe>
                                @elseif(str_contains($product->preview_content, 'vimeo'))
                                @php
                                if (strpos($product->preview_content, 'src') !== false) {
                                preg_match('/src="([^"]+)"/', $product->preview_content, $match);
                                $url = $match[1];
                                $iframe_url = str_replace('https://player.vimeo.com/video/', '', $url);
                                } else {
                                $iframe_url = str_replace('https://vimeo.com/', '', $product->preview_content);
                                }
                                @endphp
                                <iframe class="video-card-tag" width="100%" height="350"
                                    src="{{ 'https://player.vimeo.com/video/' }}{{ $iframe_url }}" frameborder="0"
                                    allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>
                                @else
                                @php
                                $iframe_url = $product->preview_content;
                                @endphp
                                <iframe class="video-card-tag" width="100%" height="100%" src="{{ $iframe_url }}"
                                    title="Video player" frameborder="0"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen></iframe>
                                @endif
                                @else
                                <video controls="">
                                    <source src="{{ get_file($product->preview_content ?? '', $currentTheme) }}" type="video/mp4">
                                </video>
                                @endif
                            </div>
                        </div>
                 

                        @include('front_end.hooks.product_tab_form')

                    </div>
                </div>
            </div>
        </section>