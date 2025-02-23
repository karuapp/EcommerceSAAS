<footer class="site-footer" style="position: relative;@if(isset($option) && $option->is_hide == 1) opacity: 0.5; @else opacity: 1; @endif" data-index="{{ $option->order ?? '' }}" data-id="{{ $option->order ?? '' }}" data-value="{{ $option->id ?? '' }}" data-hide="{{ $option->is_hide  ?? '' }}" data-section="{{ $option->section_name  ?? '' }}"  data-store="{{ $option->store_id  ?? '' }}" data-theme="{{ $option->theme_id ?? '' }}">
<div class="custome_tool_bar"></div>
    <div class="container">
        @include('front_end.hooks.footer_link')
        <div class="footer-row">
            <div class="footer-col footer-store-detail">
                <div class="footer-widget">
                    <div class="footer-logo">
                        <a href="{{ route('landing_page', $slug) }}">
                        <img src="{{ get_file(((isset($theme_logo) && !empty($theme_logo)) ? $theme_logo : 'themes/' . $currentTheme . '/assets/images/logo.png'), $currentTheme) }}"
                                    alt="Logo">
                        </a>
                    </div>
                    @if(isset($section->footer->section->description))
                    <div class="store-detail">
                        <p id="{{ $section->footer->section->description->slug ?? '' }}_preview"> {!! $section->footer->section->description->text ?? '' !!}</p>
                    </div>
                    @endif
                    <div class="social-media">
                        @if(isset($section->footer->section->footer_link))
                        <ul class="social-links">
                            @for ($i = 0; $i < $section->footer->section->footer_link->loop_number ?? 1; $i++)
                                <li>
                                    <a href="{{ $section->footer->section->footer_link->social_link->{$i} ?? '#'}}" target="_blank" id="social_link_{{ $i }}">
                                        <img src="{{ get_file($section->footer->section->footer_link->social_icon->{$i}->image ?? 'themes/' . $currentTheme . '/assets/images/youtube.svg', $currentTheme) }}" class="{{ 'social_icon_'. $i .'_preview' }}" alt="icon" id="social_icon_{{ $i }}">
                                    </a>
                                </li>
                            @endfor
                        </ul>
                        @endif
                    </div>
                </div>
            </div>
            @if(isset($section->footer->section->footer_menu_type))
                @for ($i = 0; $i < $section->footer->section->footer_menu_type->loop_number ?? 1; $i++)
                <div class="footer-col footer-link footer-link-1">
                    <div class="footer-widget">
                        <h4> {{ $section->footer->section->footer_menu_type->footer_title->{$i} ?? ''}} </h4>
                        @php
                            $footer_menu_id = $section->footer->section->footer_menu_type->footer_menu_ids->{$i} ?? '';
                            $footer_menu = get_nav_menu($footer_menu_id);
                        @endphp
                        <ul>
                            @if (!empty($footer_menu))
                                @foreach ($footer_menu as $key => $nav)
                                    @if ($nav->type == 'custom')
                                        <li><a href="{{ url($nav->slug) }}"
                                                target="{{ $nav->target }}">
                                                @if ($nav->title == null)
                                                    {{ $nav->title }}
                                                @else
                                                    {{ $nav->title }}
                                                @endif
                                            </a></li>
                                    @elseif($nav->type == 'category')
                                        <li><a href="{{ url($slug.'/'.$nav->slug) }}"
                                                target="{{ $nav->target }}">
                                                @if ($nav->title == null)
                                                    {{ $nav->title }}
                                                @else
                                                    {{ $nav->title }}
                                                @endif
                                            </a></li>
                                    @elseif($nav->type == 'brand')
                                        <li><a href="{{ url($slug.'/'.$nav->slug) }}"
                                                target="{{ $nav->target }}">
                                                @if ($nav->title == null)
                                                    {{ $nav->title }}
                                                @else
                                                    {{ $nav->title }}
                                                @endif
                                            </a></li>
                                    @else
                                        <li><a href="{{ url($slug.'/custom/'.$nav->slug) }}"
                                                target="{{ $nav->target }}">
                                                @if ($nav->title == null)
                                                    {{ $nav->title }}
                                                @else
                                                    {{ $nav->title }}
                                                @endif
                                            </a>
                                        </li>
                                    @endif
                                @endforeach
                            @endif
                        </ul>
                    </div>
                </div>
                @endfor
            @endif

        </div>
        <div class="footer-bottom">
            <div class="row align-items-center">
                <div class="col-12 col-md-6">
                    <p id="{{$section->footer->section->copy_right->slug ?? ''}}_preview">{{  $section->footer->section->copy_right->text ?? __('© 2022 Foodmart. All rights reserved')}}</p>
                </div>
                <div class="col-12 col-md-6">
                    <ul class="policy-links d-flex align-items-center justify-content-end">
                        <li><a href="{{ $section->footer->section->privacy_policy->link ?? '#' }}"><span id="{{ $section->footer->section->privacy_policy->slug ?? '' }}_preview">{{ $section->footer->section->privacy_policy->text ?? __('Policy Privacy')}}</span></a></li>
                        <li><a href="{{ $section->footer->section->terms_and_conditions->link ?? '#' }}"><span id="{{ $section->footer->section->terms_and_conditions->slug ?? '' }}_preview">{{ $section->footer->section->terms_and_conditions->text ?? __('Terms and conditions')}}</span></a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</footer>




