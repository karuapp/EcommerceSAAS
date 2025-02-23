
@if (isset($json_data->section))
    {{ Form::open(['route' => ['home.page.setting'], 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
    <input type="hidden" name="section_name" value="{{ $json_data->section_slug }}">
    <input type="hidden" name="theme_id" value="{{ $currentTheme }}">
    <input type="hidden" name="array[section_name]" value="{{ $json_data->section_name }}">
    <input type="hidden" name="array[section_slug]" value="{{ $json_data->section_slug }}">
    <input type="hidden" name="array[unique_section_slug]" value="{{ $json_data->unique_section_slug }}">
    <input type="hidden" name="array[section_enable]" value="{{ $json_data->section_enable }}">
    <input type="hidden" name="array[array_type]" value="{{ $json_data->array_type }}">
    <input type="hidden" name="array[loop_number]" value="{{ $json_data->loop_number ?? 1 }}" id="slider-loop-number">

    <div class="card sidebar-card">
        <div class="card-header d-flex justify-content-between">
            <div>
                <h5> {{ $json_data->section_name }} </h5>
            </div>
        </div>
        <div class="card-body slider-body-rows">
            @if(isset($json_data->section->background_image))
            <div class="row">
                <div class="col-sm-12">
                    <div class="form-group">
                        <label class="form-label">{{ $json_data->section->background_image->lable }}</label>
                        <input type="hidden" name="array[section][background_image][slug]" class="form-control" value="{{ $json_data->section->background_image->slug ?? '' }}">
                        <input type="hidden" name="array[section][background_image][lable]" class="form-control" value="{{ $json_data->section->background_image->lable ?? '' }}">
                        <input type="hidden" name="array[section][background_image][type]" class="form-control" value="{{ $json_data->section->background_image->type ?? '' }}">
                        <input type="hidden" name="array[section][background_image][placeholder]" class="form-control" value="{{ $json_data->section->background_image->placeholder ?? '' }}">
                        <input type="hidden" name="array[section][background_image][image]" class="form-control" value="{{ $json_data->section->background_image->image ?? '' }}">
                        <input type="file" name="array[section][background_image][text]" class="form-control" value="{{ $json_data->section->background_image->text ?? '' }}"
                                placeholder="{{ $json_data->section->background_image->placeholder }}" id="{{ $json_data->section->background_image->slug }}" accept="*">

                        <img src="{{ asset($json_data->section->background_image->image) }}" style="width:80%; height: 200px;object-fit: cover;margin-top: 10px;" class="{{ $json_data->section->background_image->slug.'_preview' }}" accept="image/*">

                    </div>
                </div>
            </div>
            @endif

            @if(isset($json_data->section->background_image_second))
            <div class="row">
                <div class="col-sm-12">
                    <div class="form-group">
                        <label class="form-label">{{ $json_data->section->background_image_second->lable }}</label>
                        <input type="hidden" name="array[section][background_image_second][slug]" class="form-control" value="{{ $json_data->section->background_image_second->slug ?? '' }}">
                        <input type="hidden" name="array[section][background_image][lable]" class="form-control" value="{{ $json_data->section->background_image_second->lable ?? '' }}">
                        <input type="hidden" name="array[section][background_image_second][type]" class="form-control" value="{{ $json_data->section->background_image_second->type ?? '' }}">
                        <input type="hidden" name="array[section][background_image_second][placeholder]" class="form-control" value="{{ $json_data->section->background_image_second->placeholder ?? '' }}">
                        <input type="hidden" name="array[section][background_image_second][image]" class="form-control" value="{{ $json_data->section->background_image_second->image ?? '' }}">
                        <input type="file" name="array[section][background_image_second][text]" class="form-control" value="{{ $json_data->section->background_image_second->text ?? '' }}"
                                placeholder="{{ $json_data->section->background_image_second->placeholder }}" id="{{ $json_data->section->background_image_second->slug }}">

                        <img src="{{ asset($json_data->section->background_image_second->image) }}" style="width:80%; height: 200px;object-fit: cover;margin-top: 10px;" class="{{ $json_data->section->background_image_second->slug.'_preview' }}" accept="image/*">

                    </div>
                </div>
            </div>
            @endif
            @if (isset($json_data->section->background_text))
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label
                                class="form-label">{{ $json_data->section->background_text->lable }}</label>
                            <input type="hidden" name="array[section][background_text][slug]"
                                class="form-control"
                                value="{{ $json_data->section->background_text->slug ?? '' }}">
                            <input type="hidden" name="array[section][background_text][lable]"
                                class="form-control"
                                value="{{ $json_data->section->background_text->lable ?? '' }}">
                            <input type="hidden" name="array[section][background_text][type]"
                                class="form-control"
                                value="{{ $json_data->section->background_text->type ?? '' }}">
                            <input type="hidden" name="array[section][background_text][placeholder]"
                                class="form-control"
                                value="{{ $json_data->section->background_text->placeholder ?? '' }}">
                            <input type="hidden" name="array[section][background_text][image]"
                                class="form-control"
                                value="{{ $json_data->section->background_text->image ?? '' }}">
                            <input type="text" name="array[section][background_text][text]"
                                class="form-control"
                                value="{{ $json_data->section->background_text->text ?? '' }}"
                                placeholder="{{ $json_data->section->background_text->placeholder }}"
                                id="{{ $json_data->section->background_text->slug }}">
                        </div>
                    </div>
                </div>
            @endif
            @if (isset($json_data->section->service_title))
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label
                                class="form-label">{{ $json_data->section->service_title->lable }}</label>
                            <input type="hidden" name="array[section][service_title][slug]"
                                class="form-control"
                                value="{{ $json_data->section->service_title->slug ?? '' }}">
                            <input type="hidden" name="array[section][service_title][lable]"
                                class="form-control"
                                value="{{ $json_data->section->service_title->lable ?? '' }}">
                            <input type="hidden" name="array[section][service_title][type]"
                                class="form-control"
                                value="{{ $json_data->section->service_title->type ?? '' }}">
                            <input type="hidden" name="array[section][service_title][placeholder]"
                                class="form-control"
                                value="{{ $json_data->section->service_title->placeholder ?? '' }}">
                            <input type="hidden" name="array[section][service_title][image]"
                                class="form-control"
                                value="{{ $json_data->section->service_title->image ?? '' }}">
                            <input type="text" name="array[section][service_title][text]"
                                class="form-control"
                                value="{{ $json_data->section->service_title->text ?? '' }}"
                                placeholder="{{ $json_data->section->service_title->placeholder }}"
                                id="{{ $json_data->section->service_title->slug }}">
                        </div>
                    </div>
                </div>
            @endif
            @if (isset($json_data->section->service_sub_title))
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label
                                class="form-label">{{ $json_data->section->service_sub_title->lable }}</label>
                            <input type="hidden" name="array[section][service_sub_title][slug]"
                                class="form-control"
                                value="{{ $json_data->section->service_sub_title->slug ?? '' }}">
                            <input type="hidden" name="array[section][service_sub_title][lable]"
                                class="form-control"
                                value="{{ $json_data->section->service_sub_title->lable ?? '' }}">
                            <input type="hidden" name="array[section][service_sub_title][type]"
                                class="form-control"
                                value="{{ $json_data->section->service_sub_title->type ?? '' }}">
                            <input type="hidden" name="array[section][service_sub_title][placeholder]"
                                class="form-control"
                                value="{{ $json_data->section->service_sub_title->placeholder ?? '' }}">
                            <input type="hidden" name="array[section][service_sub_title][image]"
                                class="form-control"
                                value="{{ $json_data->section->service_sub_title->image ?? '' }}">
                            <input type="text" name="array[section][service_sub_title][text]"
                                class="form-control"
                                value="{{ $json_data->section->service_sub_title->text ?? '' }}"
                                placeholder="{{ $json_data->section->service_sub_title->placeholder }}"
                                id="{{ $json_data->section->service_sub_title->slug }}">
                        </div>
                    </div>
                </div>
            @endif
            @if (isset($json_data->section->service_button))
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label
                                class="form-label">{{ $json_data->section->service_button->lable }}</label>
                            <input type="hidden" name="array[section][service_button][slug]"
                                class="form-control"
                                value="{{ $json_data->section->service_button->slug ?? '' }}">
                            <input type="hidden" name="array[section][service_button][lable]"
                                class="form-control"
                                value="{{ $json_data->section->service_button->lable ?? '' }}">
                            <input type="hidden" name="array[section][service_button][type]"
                                class="form-control"
                                value="{{ $json_data->section->service_button->type ?? '' }}">
                            <input type="hidden" name="array[section][service_button][placeholder]"
                                class="form-control"
                                value="{{ $json_data->section->service_button->placeholder ?? '' }}">
                            <input type="hidden" name="array[section][service_button][image]"
                                class="form-control"
                                value="{{ $json_data->section->service_button->image ?? '' }}">
                            <input type="text" name="array[section][service_button][text]"
                                class="form-control"
                                value="{{ $json_data->section->service_button->text ?? '' }}"
                                placeholder="{{ $json_data->section->service_button->placeholder }}"
                                id="{{ $json_data->section->service_button->slug }}">
                        </div>
                    </div>
                </div>
            @endif
            @if (isset($json_data->section->service_description))
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label
                                class="form-label">{{ $json_data->section->service_description->lable }}</label>
                            <input type="hidden" name="array[section][service_description][slug]"
                                class="form-control"
                                value="{{ $json_data->section->service_description->slug ?? '' }}">
                            <input type="hidden" name="array[section][service_description][lable]"
                                class="form-control"
                                value="{{ $json_data->section->service_description->lable ?? '' }}">
                            <input type="hidden" name="array[section][service_description][type]"
                                class="form-control"
                                value="{{ $json_data->section->service_description->type ?? '' }}">
                            <input type="hidden" name="array[section][service_description][placeholder]"
                                class="form-control"
                                value="{{ $json_data->section->service_description->placeholder ?? '' }}">
                            <input type="hidden" name="array[section][service_description][image]"
                                class="form-control"
                                value="{{ $json_data->section->service_description->image ?? '' }}">
                            <textarea name="array[section][service_description][text]"
                                class="form-control"
                                placeholder="{{ $json_data->section->service_description->placeholder }}"
                                id="{{ $json_data->section->service_description->slug }}"> {{ $json_data->section->service_description->text ?? '' }} </textarea>
                        </div>
                    </div>
                </div>
            @endif
            @if(isset($json_data->section->service_image))
            <div class="row">
                <div class="col-sm-12">
                    <div class="form-group">
                        <label class="form-label">{{ $json_data->section->service_image->lable }}</label>
                        <input type="hidden" name="array[section][service_image][slug]" class="form-control" value="{{ $json_data->section->service_image->slug ?? '' }}">
                        <input type="hidden" name="array[section][service_image][lable]" class="form-control" value="{{ $json_data->section->service_image->lable ?? '' }}">
                        <input type="hidden" name="array[section][service_image][type]" class="form-control" value="{{ $json_data->section->service_image->type ?? '' }}">
                        <input type="hidden" name="array[section][service_image][placeholder]" class="form-control" value="{{ $json_data->section->service_image->placeholder ?? '' }}">
                        <input type="hidden" name="array[section][service_image][image]" class="form-control" value="{{ $json_data->section->service_image->image ?? '' }}">
                        <input type="file" name="array[section][service_image][text]" class="form-control" value="{{ $json_data->section->service_image->text ?? '' }}"
                                placeholder="{{ $json_data->section->service_image->placeholder }}" id="{{ $json_data->section->service_image->slug }}">

                        <img src="{{ asset($json_data->section->service_image->image) }}" style="width:80%; height: 200px;object-fit: cover;margin-top: 10px;" class="{{ $json_data->section->service_image->slug.'_preview' }}" accept="image/*">

                    </div>
                </div>
            </div>
            @endif

            @if (isset($json_data->section->service_second_title))
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label
                                class="form-label">{{ $json_data->section->service_second_title->lable }}</label>
                            <input type="hidden" name="array[section][service_second_title][slug]"
                                class="form-control"
                                value="{{ $json_data->section->service_second_title->slug ?? '' }}">
                            <input type="hidden" name="array[section][service_second_title][lable]"
                                class="form-control"
                                value="{{ $json_data->section->service_second_title->lable ?? '' }}">
                            <input type="hidden" name="array[section][service_second_title][type]"
                                class="form-control"
                                value="{{ $json_data->section->service_second_title->type ?? '' }}">
                            <input type="hidden" name="array[section][service_second_title][placeholder]"
                                class="form-control"
                                value="{{ $json_data->section->service_second_title->placeholder ?? '' }}">
                            <input type="hidden" name="array[section][service_second_title][image]"
                                class="form-control"
                                value="{{ $json_data->section->service_second_title->image ?? '' }}">
                            <input type="text" name="array[section][service_second_title][text]"
                                class="form-control"
                                value="{{ $json_data->section->service_second_title->text ?? '' }}"
                                placeholder="{{ $json_data->section->service_second_title->placeholder }}"
                                id="{{ $json_data->section->service_second_title->slug }}">
                        </div>
                    </div>
                </div>
            @endif

            @if (isset($json_data->section->service_second_description))
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label
                                class="form-label">{{ $json_data->section->service_second_description->lable }}</label>
                            <input type="hidden" name="array[section][service_second_description][slug]"
                                class="form-control"
                                value="{{ $json_data->section->service_second_description->slug ?? '' }}">
                            <input type="hidden" name="array[section][service_second_description][lable]"
                                class="form-control"
                                value="{{ $json_data->section->service_second_description->lable ?? '' }}">
                            <input type="hidden" name="array[section][service_second_description][type]"
                                class="form-control"
                                value="{{ $json_data->section->service_second_description->type ?? '' }}">
                            <input type="hidden" name="array[section][service_second_description][placeholder]"
                                class="form-control"
                                value="{{ $json_data->section->service_second_description->placeholder ?? '' }}">
                            <input type="hidden" name="array[section][service_second_description][image]"
                                class="form-control"
                                value="{{ $json_data->section->service_second_description->image ?? '' }}">
                            <textarea name="array[section][service_second_description][text]"
                                class="form-control"
                                placeholder="{{ $json_data->section->service_second_description->placeholder }}"
                                id="{{ $json_data->section->service_second_description->slug }}"> {{ $json_data->section->service_second_description->text ?? '' }} </textarea>
                        </div>
                    </div>
                </div>
            @endif

            @if(isset($json_data->section->service_second_image))
            <div class="row">
                <div class="col-sm-12">
                    <div class="form-group">
                        <label class="form-label">{{ $json_data->section->service_second_image->lable }}</label>
                        <input type="hidden" name="array[section][service_second_image][slug]" class="form-control" value="{{ $json_data->section->service_second_image->slug ?? '' }}">
                        <input type="hidden" name="array[section][service_second_image][lable]" class="form-control" value="{{ $json_data->section->service_second_image->lable ?? '' }}">
                        <input type="hidden" name="array[section][service_second_image][type]" class="form-control" value="{{ $json_data->section->service_second_image->type ?? '' }}">
                        <input type="hidden" name="array[section][service_second_image][placeholder]" class="form-control" value="{{ $json_data->section->service_second_image->placeholder ?? '' }}">
                        <input type="hidden" name="array[section][service_second_image][image]" class="form-control" value="{{ $json_data->section->service_second_image->image ?? '' }}">
                        <input type="file" name="array[section][service_second_image][text]" class="form-control" value="{{ $json_data->section->service_second_image->text ?? '' }}"
                                placeholder="{{ $json_data->section->service_second_image->placeholder }}" id="{{ $json_data->section->service_second_image->slug }}">

                        <img src="{{ asset($json_data->section->service_second_image->image) }}" style="width:80%; height: 200px;object-fit: cover;margin-top: 10px;" class="{{ $json_data->section->service_second_image->slug.'_preview' }}" accept="image/*">

                    </div>
                </div>
            </div>
            @endif

            @if (isset($json_data->section->service_third_title))
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label
                                class="form-label">{{ $json_data->section->service_third_title->lable }}</label>
                            <input type="hidden" name="array[section][service_third_title][slug]"
                                class="form-control"
                                value="{{ $json_data->section->service_third_title->slug ?? '' }}">
                            <input type="hidden" name="array[section][service_third_title][lable]"
                                class="form-control"
                                value="{{ $json_data->section->service_third_title->lable ?? '' }}">
                            <input type="hidden" name="array[section][service_third_title][type]"
                                class="form-control"
                                value="{{ $json_data->section->service_third_title->type ?? '' }}">
                            <input type="hidden" name="array[section][service_third_title][placeholder]"
                                class="form-control"
                                value="{{ $json_data->section->service_third_title->placeholder ?? '' }}">
                            <input type="hidden" name="array[section][service_third_title][image]"
                                class="form-control"
                                value="{{ $json_data->section->service_third_title->image ?? '' }}">
                            <input type="text" name="array[section][service_third_title][text]"
                                class="form-control"
                                value="{{ $json_data->section->service_third_title->text ?? '' }}"
                                placeholder="{{ $json_data->section->service_third_title->placeholder }}"
                                id="{{ $json_data->section->service_third_title->slug }}">
                        </div>
                    </div>
                </div>
            @endif

            @if (isset($json_data->section->service_third_description))
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label
                                class="form-label">{{ $json_data->section->service_third_description->lable }}</label>
                            <input type="hidden" name="array[section][service_third_description][slug]"
                                class="form-control"
                                value="{{ $json_data->section->service_third_description->slug ?? '' }}">
                            <input type="hidden" name="array[section][service_third_description][lable]"
                                class="form-control"
                                value="{{ $json_data->section->service_third_description->lable ?? '' }}">
                            <input type="hidden" name="array[section][service_third_description][type]"
                                class="form-control"
                                value="{{ $json_data->section->service_third_description->type ?? '' }}">
                            <input type="hidden" name="array[section][service_third_description][placeholder]"
                                class="form-control"
                                value="{{ $json_data->section->service_third_description->placeholder ?? '' }}">
                            <input type="hidden" name="array[section][service_third_description][image]"
                                class="form-control"
                                value="{{ $json_data->section->service_third_description->image ?? '' }}">
                            <textarea name="array[section][service_third_description][text]"
                                class="form-control"
                                placeholder="{{ $json_data->section->service_third_description->placeholder }}"
                                id="{{ $json_data->section->service_third_description->slug }}"> {{ $json_data->section->service_third_description->text ?? '' }} </textarea>
                        </div>
                    </div>
                </div>
            @endif

            @if(isset($json_data->section->service_third_image))
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label class="form-label">{{ $json_data->section->service_third_image->lable }}</label>
                            <input type="hidden" name="array[section][service_third_image][slug]" class="form-control" value="{{ $json_data->section->service_third_image->slug ?? '' }}">
                            <input type="hidden" name="array[section][service_third_image][lable]" class="form-control" value="{{ $json_data->section->service_third_image->lable ?? '' }}">
                            <input type="hidden" name="array[section][service_third_image][type]" class="form-control" value="{{ $json_data->section->service_third_image->type ?? '' }}">
                            <input type="hidden" name="array[section][service_third_image][placeholder]" class="form-control" value="{{ $json_data->section->service_third_image->placeholder ?? '' }}">
                            <input type="hidden" name="array[section][service_third_image][image]" class="form-control" value="{{ $json_data->section->service_third_image->image ?? '' }}">
                            <input type="file" name="array[section][service_third_image][text]" class="form-control" value="{{ $json_data->section->service_third_image->text ?? '' }}"
                                    placeholder="{{ $json_data->section->service_third_image->placeholder }}" id="{{ $json_data->section->service_third_image->slug }}">

                            <img src="{{ asset($json_data->section->service_third_image->image) }}" style="width:80%; height: 200px;object-fit: cover;margin-top: 10px;" class="{{ $json_data->section->service_third_image->slug.'_preview' }}" accept="image/*">

                        </div>
                    </div>
                </div>
             @endif

             @if (isset($json_data->section->show_more_button))
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label
                                class="form-label">{{ $json_data->section->show_more_button->lable }}</label>
                            <input type="hidden" name="array[section][show_more_button][slug]"
                                class="form-control"
                                value="{{ $json_data->section->show_more_button->slug ?? '' }}">
                            <input type="hidden" name="array[section][show_more_button][lable]"
                                class="form-control"
                                value="{{ $json_data->section->show_more_button->lable ?? '' }}">
                            <input type="hidden" name="array[section][show_more_button][type]"
                                class="form-control"
                                value="{{ $json_data->section->show_more_button->type ?? '' }}">
                            <input type="hidden" name="array[section][show_more_button][placeholder]"
                                class="form-control"
                                value="{{ $json_data->section->show_more_button->placeholder ?? '' }}">
                            <input type="hidden" name="array[section][show_more_button][image]"
                                class="form-control"
                                value="{{ $json_data->section->show_more_button->image ?? '' }}">
                            <input type="text" name="array[section][show_more_button][text]"
                                class="form-control"
                                value="{{ $json_data->section->show_more_button->text ?? '' }}"
                                placeholder="{{ $json_data->section->show_more_button->placeholder }}"
                                id="{{ $json_data->section->show_more_button->slug }}">
                        </div>
                    </div>
                </div>
            @endif

            @if (isset($json_data->section->check_more_button))
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label
                                class="form-label">{{ $json_data->section->check_more_button->lable }}</label>
                            <input type="hidden" name="array[section][check_more_button][slug]"
                                class="form-control"
                                value="{{ $json_data->section->check_more_button->slug ?? '' }}">
                            <input type="hidden" name="array[section][check_more_button][lable]"
                                class="form-control"
                                value="{{ $json_data->section->check_more_button->lable ?? '' }}">
                            <input type="hidden" name="array[section][check_more_button][type]"
                                class="form-control"
                                value="{{ $json_data->section->check_more_button->type ?? '' }}">
                            <input type="hidden" name="array[section][check_more_button][placeholder]"
                                class="form-control"
                                value="{{ $json_data->section->check_more_button->placeholder ?? '' }}">
                            <input type="hidden" name="array[section][check_more_button][image]"
                                class="form-control"
                                value="{{ $json_data->section->check_more_button->image ?? '' }}">
                            <input type="text" name="array[section][check_more_button][text]"
                                class="form-control"
                                value="{{ $json_data->section->check_more_button->text ?? '' }}"
                                placeholder="{{ $json_data->section->check_more_button->placeholder }}"
                                id="{{ $json_data->section->check_more_button->slug }}">
                        </div>
                    </div>
                </div>
            @endif

            @if(isset($json_data->section->video_title))
            <div class="row">
                <div class="col-sm-12">
                    <div class="form-group">
                        <label class="form-label">{{ $json_data->section->video_title->lable }}</label>
                        <input type="hidden" name="array[section][video_title][slug]" class="form-control" value="{{ $json_data->section->video_title->slug ?? '' }}">
                        <input type="hidden" name="array[section][video_title][lable]" class="form-control" value="{{ $json_data->section->video_title->lable ?? '' }}">
                        <input type="hidden" name="array[section][video_title][type]" class="form-control" value="{{ $json_data->section->video_title->type ?? '' }}">
                        <input type="hidden" name="array[section][video_title][text]" class="form-control" value="{{ $json_data->section->video_title->text ?? '' }}">
                        <input type="hidden" name="array[section][video_title][loop_number]" class="form-control" value="{{ $json_data->section->video_title->loop_number ?? '' }}">
                        <hr/>
                        @if (isset($json_data->section->video_title->type) && ($json_data->section->video_title->type == 'array') && isset($json_data->section->video_title->loop_number))
                        @for($i=0; $i<$json_data->section->video_title->loop_number; $i++)
                            <input type="text" name="array[section][video_title][annouce_text][{{$i}}][text]"
                            class="form-control"
                            value="{{ $json_data->section->video_title->annouce_text->{$i}->text ?? '' }}"
                            placeholder="{{ $json_data->section->video_title->placeholder }}"
                            id="{{ $json_data->section->video_title->slug.'_'.$i }}">
                            @endfor
                        @endif
                    </div>
                </div>
            </div>
            @endif
            @if(isset($json_data->section->video))
            <div class="row">
                <div class="col-sm-12">
                    <div class="form-group">
                        <label class="form-label">{{ $json_data->section->video->lable }}</label>
                        <input type="hidden" name="array[section][video][slug]" class="form-control" value="{{ $json_data->section->video->slug ?? '' }}">
                        <input type="hidden" name="array[section][video][lable]" class="form-control" value="{{ $json_data->section->video->lable ?? '' }}">
                        <input type="hidden" name="array[section][video][placeholder]" class="form-control" value="{{ $json_data->section->video->placeholder ?? '' }}">

                        <!-- Option to select file or link -->
                        <div class="form-group">
                            <label for="video_type">{{ __('Select Video Type') }}</label>
                            <select id="video_type" name="array[section][video][type]" class="form-control" onchange="toggleVideoType()">
                                <option value="file" {{ $json_data->section->video->type == 'file' ? 'selected' : '' }}>{{ __('Upload File') }}</option>
                                <option value="text" {{ $json_data->section->video->type == 'text' ? 'selected' : '' }}>{{ __('Provide Link') }}</option>
                            </select>
                        </div>

                        <input type="hidden" name="array[section][video][image]" class="form-control" value="{{ $json_data->section->video->image ?? '' }}">

                        <!-- File Upload Field -->
                        <div id="file_upload_section" style="display: {{ $json_data->section->video->type == 'file' ? 'block' : 'none' }}">
                            <input type="file" name="array[section][video][text]" class="form-control" placeholder="{{ $json_data->section->video->placeholder }}" accept="video/*">
                            @if($json_data->section->video->image)
                                <video src="{{ asset($json_data->section->video->image) }}" controls loop autoplay muted style="width:80%; height: 200px;object-fit: cover;margin-top: 10px;"></video>
                            @endif
                        </div>

                        <!-- Video Link Input Field -->
                        <div id="link_upload_section" style="display: {{ $json_data->section->video->type == 'text' ? 'block' : 'none' }}">
                            <input type="url" name="array[section][video][text]" class="form-control" placeholder="{{ __('Please provide the video link') }}" value="{{ $json_data->section->video->text ?? '' }}">
                            @if($json_data->section->video->text)
                                <video src="{{ $json_data->section->video->text }}" controls loop autoplay muted style="width:80%; height: 200px;object-fit: cover;margin-top: 10px;"></video>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @if (isset($json_data->section->slider_sub_description))
                <div class="col-sm-12">
                    <div class="form-group">
                        <label
                            class="form-label">{{ $json_data->section->slider_sub_description->lable }}</label>
                        <input type="hidden"
                            name="array[section][slider_sub_description][slug]"
                            class="form-control"
                            value="{{ $json_data->section->slider_sub_description->slug ?? '' }}">
                        <input type="hidden"
                            name="array[section][slider_sub_description][lable]"
                            class="form-control"
                            value="{{ $json_data->section->slider_sub_description->lable ?? '' }}">
                        <input type="hidden"
                            name="array[section][slider_sub_description][type]"
                            class="form-control"
                            value="{{ $json_data->section->slider_sub_description->type ?? '' }}">
                        <input type="hidden"
                            name="array[section][slider_sub_description][placeholder]"
                            class="form-control"
                            value="{{ $json_data->section->slider_sub_description->placeholder ?? '' }}">
                        <textarea type="text" name="array[section][slider_sub_description][text]" class="form-control"
                            placeholder="{{ $json_data->section->slider_sub_description->placeholder }}"
                            id="{{ $json_data->section->slider_sub_description->slug }}"> {{ $json_data->section->slider_sub_description->text ?? '' }} </textarea>

                    </div>
                </div>
            @endif

            @if (isset($json_data->section->sub_description))
                <div class="col-sm-12">
                    <div class="form-group">
                        <label
                            class="form-label">{{ $json_data->section->sub_description->lable }}</label>
                        <input type="hidden"
                            name="array[section][sub_description][slug]"
                            class="form-control"
                            value="{{ $json_data->section->sub_description->slug ?? '' }}">
                        <input type="hidden"
                            name="array[section][sub_description][lable]"
                            class="form-control"
                            value="{{ $json_data->section->sub_description->lable ?? '' }}">
                        <input type="hidden"
                            name="array[section][sub_description][type]"
                            class="form-control"
                            value="{{ $json_data->section->sub_description->type ?? '' }}">
                        <input type="hidden"
                            name="array[section][sub_description][placeholder]"
                            class="form-control"
                            value="{{ $json_data->section->sub_description->placeholder ?? '' }}">
                        <textarea type="text" name="array[section][sub_description][text]" class="form-control"
                            placeholder="{{ $json_data->section->sub_description->placeholder }}"
                            id="{{ $json_data->section->sub_description->slug }}"> {{ $json_data->section->sub_description->text ?? '' }} </textarea>

                    </div>
                </div>
            @endif
            @for($i=0; $i< $json_data->loop_number; $i++)
            <div class="row slider_{{$i}}" data-slider-index="{{$i}}">
                @if($json_data->array_type == 'multi-inner-list')
                    @if($json_data->section_name == 'Homepage Slider')
                        <div class="accordion accordion-flush" id="accordionFlushExample">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="flush-headingOne{{$i}}">
                                    <button class="accordion-button collapsed slider-collspan" type="button" data-bs-toggle="collapse" data-bs-target="#{{$json_data->section_slug . '_'. $i}}" aria-expanded="false" aria-controls="{{$json_data->section_slug . '_'. $i}}">
                                        {{$json_data->section_name}}
                                    </button>
                                </h2>
                                <div id="{{$json_data->section_slug . '_'.$i}}" class="accordion-collapse collapse slider-collspan-body" aria-labelledby="flush-headingOne{{$i}}" data-bs-parent="#accordionFlushExample">
                                    <div class="accordion-body">

                                        @if(isset($json_data->section->title))
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <label class="form-label">{{ $json_data->section->title->lable }}</label>
                                                <input type="hidden" name="array[section][title][slug]" class="form-control" value="{{ $json_data->section->title->slug ?? '' }}">
                                                <input type="hidden" name="array[section][title][lable]" class="form-control" value="{{ $json_data->section->title->lable ?? '' }}">
                                                <input type="hidden" name="array[section][title][type]" class="form-control" value="{{ $json_data->section->title->type ?? '' }}">
                                                <input type="hidden" name="array[section][title][placeholder]" class="form-control" value="{{ $json_data->section->title->placeholder ?? '' }}">
                                                <input type="text" name="array[section][title][text][{{$i}}]" class="form-control" value="{{ $json_data->section->title->text->{$i} ?? '' }}"
                                                        placeholder="{{ $json_data->section->title->placeholder }}" id="{{ $json_data->section->title->slug.'_'.$i }}">

                                            </div>
                                        </div>
                                        @endif
                                        @if(isset($json_data->section->sub_title))
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <label class="form-label">{{ $json_data->section->sub_title->lable }}</label>
                                                <input type="hidden" name="array[section][sub_title][slug]" class="form-control" value="{{ $json_data->section->sub_title->slug ?? '' }}">
                                                <input type="hidden" name="array[section][sub_title][lable]" class="form-control" value="{{ $json_data->section->sub_title->lable ?? '' }}">
                                                <input type="hidden" name="array[section][sub_title][type]" class="form-control" value="{{ $json_data->section->sub_title->type ?? '' }}">
                                                <input type="hidden" name="array[section][sub_title][placeholder]" class="form-control" value="{{ $json_data->section->sub_title->placeholder ?? '' }}">
                                                <input type="text" name="array[section][sub_title][text][{{$i}}]" class="form-control" value="{{ $json_data->section->sub_title->text->{$i} ?? '' }}"
                                                        placeholder="{{ $json_data->section->sub_title->placeholder }}" id="{{ $json_data->section->sub_title->slug.'_'.$i }}">

                                            </div>
                                        </div>
                                        @endif
                                        @if(isset($json_data->section->button_first))
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <label class="form-label">{{ $json_data->section->button_first->lable }}</label>
                                                <input type="hidden" name="array[section][button_first][slug]" class="form-control" value="{{ $json_data->section->button_first->slug ?? '' }}">
                                                <input type="hidden" name="array[section][button_first][lable]" class="form-control" value="{{ $json_data->section->button_first->lable ?? '' }}">
                                                <input type="hidden" name="array[section][button_first][type]" class="form-control" value="{{ $json_data->section->button_first->type ?? '' }}">
                                                <input type="hidden" name="array[section][button_first][placeholder]" class="form-control" value="{{ $json_data->section->button_first->placeholder ?? '' }}">
                                                <input type="text" name="array[section][button_first][text][{{$i}}]" class="form-control" value="{{ $json_data->section->button_first->text->{$i} ?? '' }}"
                                                        placeholder="{{ $json_data->section->button_first->placeholder }}" id="{{ $json_data->section->button_first->slug.'_'.$i }}">

                                            </div>
                                        </div>
                                        @endif

                                        @if(isset($json_data->section->button))
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <label class="form-label">{{ $json_data->section->button->lable }}</label>
                                                <input type="hidden" name="array[section][button][slug]" class="form-control" value="{{ $json_data->section->button->slug ?? '' }}">
                                                <input type="hidden" name="array[section][button][lable]" class="form-control" value="{{ $json_data->section->button->lable ?? '' }}">
                                                <input type="hidden" name="array[section][button][type]" class="form-control" value="{{ $json_data->section->button->type ?? '' }}">
                                                <input type="hidden" name="array[section][button][placeholder]" class="form-control" value="{{ $json_data->section->button->placeholder ?? '' }}">
                                                <input type="text" name="array[section][button][text][{{$i}}]" class="form-control" value="{{ $json_data->section->button->text->{$i} ?? '' }}"
                                                        placeholder="{{ $json_data->section->button->placeholder }}" id="{{ $json_data->section->button->slug.'_'.$i }}">

                                            </div>
                                        </div>
                                        @endif

                                        @if(isset($json_data->section->button_second))
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <label class="form-label">{{ $json_data->section->button_second->lable }}</label>
                                                <input type="hidden" name="array[section][button_second][slug]" class="form-control" value="{{ $json_data->section->button_second->slug ?? '' }}">
                                                <input type="hidden" name="array[section][button_second][lable]" class="form-control" value="{{ $json_data->section->button_second->lable ?? '' }}">
                                                <input type="hidden" name="array[section][button_second][type]" class="form-control" value="{{ $json_data->section->button_second->type ?? '' }}">
                                                <input type="hidden" name="array[section][button_second][placeholder]" class="form-control" value="{{ $json_data->section->button_second->placeholder ?? '' }}">
                                                <input type="text" name="array[section][button_second][text][{{$i}}]" class="form-control" value="{{ $json_data->section->button_second->text->{$i} ?? '' }}"
                                                        placeholder="{{ $json_data->section->button_second->placeholder }}" id="{{ $json_data->section->button_second->slug.'_'.$i }}">

                                            </div>
                                        </div>
                                        @endif
                                        @if(isset($json_data->section->description))
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <label class="form-label">{{ $json_data->section->description->lable }}</label>
                                                <input type="hidden" name="array[section][description][slug]" class="form-control" value="{{ $json_data->section->description->slug ?? '' }}">
                                                <input type="hidden" name="array[section][description][lable]" class="form-control" value="{{ $json_data->section->description->lable ?? '' }}">
                                                <input type="hidden" name="array[section][description][type]" class="form-control" value="{{ $json_data->section->description->type ?? '' }}">
                                                <input type="hidden" name="array[section][description][placeholder]" class="form-control" value="{{ $json_data->section->description->placeholder ?? '' }}">
                                                <textarea type="text" name="array[section][description][text][{{$i}}]" class="form-control"
                                                        placeholder="{{ $json_data->section->description->placeholder }}" id="{{ $json_data->section->description->slug.'_'.$i }}"> {{ $json_data->section->description->text->{$i} ?? ''}} </textarea>

                                            </div>
                                        </div>
                                        @endif
                                        @if(isset($json_data->section->image))
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <label class="form-label">{{ $json_data->section->image->lable }}</label>
                                                <input type="hidden" name="array[section][image][slug]" class="form-control" value="{{ $json_data->section->image->slug ?? '' }}">
                                                <input type="hidden" name="array[section][image][lable]" class="form-control" value="{{ $json_data->section->image->lable ?? '' }}">
                                                <input type="hidden" name="array[section][image][type]" class="form-control" value="{{ $json_data->section->image->type ?? '' }}">
                                                <input type="hidden" name="array[section][image][placeholder]" class="form-control" value="{{ $json_data->section->image->placeholder ?? '' }}">
                                                <input type="hidden" name="array[section][image][image][{{$i}}]" class="form-control"
                                                value="{{ $json_data->section->image->image->{$i} ?? '' }}" >
                                                <input type="file" name="array[section][image][text][{{$i}}]" class="form-control"
                                                        placeholder="{{ $json_data->section->image->placeholder }}" >
                                                 <img src="{{ asset($json_data->section->image->image->{$i} ?? '') }}" id="{{ $json_data->section->image->slug.'_'.$i }}" accept="image/*">

                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        @if(isset($json_data->section->title))
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label class="form-label">{{ $json_data->section->title->lable }}</label>
                                    <input type="hidden" name="array[section][title][slug]" class="form-control" value="{{ $json_data->section->title->slug ?? '' }}">
                                    <input type="hidden" name="array[section][title][lable]" class="form-control" value="{{ $json_data->section->title->lable ?? '' }}">
                                    <input type="hidden" name="array[section][title][type]" class="form-control" value="{{ $json_data->section->title->type ?? '' }}">
                                    <input type="hidden" name="array[section][title][placeholder]" class="form-control" value="{{ $json_data->section->title->placeholder ?? '' }}">
                                    <input type="text" name="array[section][title][text][{{$i}}]" class="form-control" value="{{ $json_data->section->title->text->{$i} ?? '' }}"
                                            placeholder="{{ $json_data->section->title->placeholder }}" id="{{ $json_data->section->title->slug.'_'.$i }}">

                                </div>
                            </div>
                        @endif
                        @if(isset($json_data->section->sub_title))
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label class="form-label">{{ $json_data->section->sub_title->lable }}</label>
                                <input type="hidden" name="array[section][sub_title][slug]" class="form-control" value="{{ $json_data->section->sub_title->slug ?? '' }}">
                                <input type="hidden" name="array[section][sub_title][lable]" class="form-control" value="{{ $json_data->section->sub_title->lable ?? '' }}">
                                <input type="hidden" name="array[section][sub_title][type]" class="form-control" value="{{ $json_data->section->sub_title->type ?? '' }}">
                                <input type="hidden" name="array[section][sub_title][placeholder]" class="form-control" value="{{ $json_data->section->sub_title->placeholder ?? '' }}">
                                <input type="text" name="array[section][sub_title][text][{{$i}}]" class="form-control" value="{{ $json_data->section->sub_title->text->{$i} ?? '' }}"
                                        placeholder="{{ $json_data->section->sub_title->placeholder }}" id="{{ $json_data->section->sub_title->slug.'_'.$i }}">

                            </div>
                        </div>
                        @endif
                        @if(isset($json_data->section->button))
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label class="form-label">{{ $json_data->section->button->lable }}</label>
                                <input type="hidden" name="array[section][button][slug]" class="form-control" value="{{ $json_data->section->button->slug ?? '' }}">
                                <input type="hidden" name="array[section][button][lable]" class="form-control" value="{{ $json_data->section->button->lable ?? '' }}">
                                <input type="hidden" name="array[section][button][type]" class="form-control" value="{{ $json_data->section->button->type ?? '' }}">
                                <input type="hidden" name="array[section][button][placeholder]" class="form-control" value="{{ $json_data->section->button->placeholder ?? '' }}">
                                <input type="text" name="array[section][button][text][{{$i}}]" class="form-control" value="{{ $json_data->section->button->text->{$i} ?? '' }}"
                                        placeholder="{{ $json_data->section->button->placeholder }}" id="{{ $json_data->section->button->slug.'_'.$i }}">

                            </div>
                        </div>
                        @endif
                        @if(isset($json_data->section->button_first))
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label class="form-label">{{ $json_data->section->button_first->lable }}</label>
                                <input type="hidden" name="array[section][button_first][slug]" class="form-control" value="{{ $json_data->section->button_first->slug ?? '' }}">
                                <input type="hidden" name="array[section][button_first][lable]" class="form-control" value="{{ $json_data->section->button_first->lable ?? '' }}">
                                <input type="hidden" name="array[section][button_first][type]" class="form-control" value="{{ $json_data->section->button_first->type ?? '' }}">
                                <input type="hidden" name="array[section][button_first][placeholder]" class="form-control" value="{{ $json_data->section->button_first->placeholder ?? '' }}">
                                <input type="text" name="array[section][button_first][text][{{$i}}]" class="form-control" value="{{ $json_data->section->button_first->text->{$i} ?? '' }}"
                                        placeholder="{{ $json_data->section->button_first->placeholder }}" id="{{ $json_data->section->button_first->slug.'_'.$i }}">

                            </div>
                        </div>
                        @endif
                        @if(isset($json_data->section->button_second))
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label class="form-label">{{ $json_data->section->button_second->lable }}</label>
                                <input type="hidden" name="array[section][button_second][slug]" class="form-control" value="{{ $json_data->section->button_second->slug ?? '' }}">
                                <input type="hidden" name="array[section][button_second][lable]" class="form-control" value="{{ $json_data->section->button_second->lable ?? '' }}">
                                <input type="hidden" name="array[section][button_second][type]" class="form-control" value="{{ $json_data->section->button_second->type ?? '' }}">
                                <input type="hidden" name="array[section][button_second][placeholder]" class="form-control" value="{{ $json_data->section->button_second->placeholder ?? '' }}">
                                <input type="text" name="array[section][button_second][text][{{$i}}]" class="form-control" value="{{ $json_data->section->button_second->text->{$i} ?? '' }}"
                                        placeholder="{{ $json_data->section->button_second->placeholder }}" id="{{ $json_data->section->button_second->slug.'_'.$i }}">

                            </div>
                        </div>
                        @endif
                        @if(isset($json_data->section->description))
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label class="form-label">{{ $json_data->section->description->lable }}</label>
                                <input type="hidden" name="array[section][description][slug]" class="form-control" value="{{ $json_data->section->description->slug ?? '' }}">
                                <input type="hidden" name="array[section][description][lable]" class="form-control" value="{{ $json_data->section->description->lable ?? '' }}">
                                <input type="hidden" name="array[section][description][type]" class="form-control" value="{{ $json_data->section->description->type ?? '' }}">
                                <input type="hidden" name="array[section][description][placeholder]" class="form-control" value="{{ $json_data->section->description->placeholder ?? '' }}">
                                <textarea type="text" name="array[section][description][text][{{$i}}]" class="form-control"
                                        placeholder="{{ $json_data->section->description->placeholder }}" id="{{ $json_data->section->description->slug.'_'.$i }}"> {{ $json_data->section->description->text->{$i} ?? '' }} </textarea>

                            </div>
                        </div>
                        @endif
                        <hr/>
                        @if(isset($json_data->section->image))
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label class="form-label">{{ $json_data->section->image->lable }}</label>
                                <input type="hidden" name="array[section][image][slug]" class="form-control" value="{{ $json_data->section->image->slug ?? '' }}">
                                <input type="hidden" name="array[section][image][lable]" class="form-control" value="{{ $json_data->section->image->lable ?? '' }}">
                                <input type="hidden" name="array[section][image][type]" class="form-control" value="{{ $json_data->section->image->type ?? '' }}">
                                <input type="hidden" name="array[section][image][placeholder]" class="form-control" value="{{ $json_data->section->image->placeholder ?? '' }}">
                                <input type="hidden" name="array[section][image][image][{{$i}}]" class="form-control"
                                value="{{ $json_data->section->image->image->{$i} ?? '' }}" >
                                <input type="file" name="array[section][image][text][{{$i}}]" class="form-control"
                                        placeholder="{{ $json_data->section->image->placeholder }}" accept="*">
                                    <img src="{{ asset($json_data->section->image->image->{$i} ?? '') }}" id="{{ $json_data->section->image->slug.'_'.$i }}" accept="image/*">

                            </div>
                        </div>
                        @endif
                    @endif
                @else
                    @if (isset($json_data->section->title))
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label class="form-label">{{ $json_data->section->title->lable }}</label>
                            <input type="hidden" name="array[section][title][slug]" class="form-control" value="{{ $json_data->section->title->slug ?? '' }}">
                            <input type="hidden" name="array[section][title][lable]" class="form-control" value="{{ $json_data->section->title->lable ?? '' }}">
                            <input type="hidden" name="array[section][title][type]" class="form-control" value="{{ $json_data->section->title->type ?? '' }}">
                            <input type="hidden" name="array[section][title][placeholder]" class="form-control" value="{{ $json_data->section->title->placeholder ?? '' }}">
                            @if (isset($json_data->section->title->text) && (is_array($json_data->section->title->text) || is_object($json_data->section->title->text)) && isset($json_data->section->title->loop_number))
                                <hr/>
                                @for($i=0; $i<$json_data->section->title->loop_number; $i++)
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label class="form-label">{{ $json_data->section->title->lable .' '. ($i+1) }}</label>
                                            <input type="text" name="array[section][title][text][{{ $i }}]" class="form-control" id="{{ ($json_data->section->title->lable ?? '').'_'.$i }}" value="{{ $json_data->section->title->text->{$i} ?? '' }}">
                                        </div>
                                    </div>
                                </div>
                                @endfor
                            @else
                            <input type="text" name="array[section][title][text]" class="form-control" value="{{ $json_data->section->title->text ?? '' }}"
                                    placeholder="{{ $json_data->section->title->placeholder }}" id="{{ $json_data->section->title->slug }}">
                            @endif
                        </div>
                    </div>
                    @endif
                    @if (isset($json_data->section->support_title))
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label class="form-label">{{ $json_data->section->support_title->lable }}</label>
                            <input type="hidden" name="array[section][support_title][slug]" class="form-control" value="{{ $json_data->section->support_title->slug ?? '' }}">
                            <input type="hidden" name="array[section][support_title][lable]" class="form-control" value="{{ $json_data->section->support_title->lable ?? '' }}">
                            <input type="hidden" name="array[section][support_title][type]" class="form-control" value="{{ $json_data->section->support_title->type ?? '' }}">
                            <input type="hidden" name="array[section][support_title][placeholder]" class="form-control" value="{{ $json_data->section->support_title->placeholder ?? '' }}">


                            @if (isset($json_data->section->support_title->text) && (is_array($json_data->section->support_title->text) || is_object($json_data->section->support_title->text)) && isset($json_data->section->support_title->loop_number))
                                <hr/>
                                @for($i=0; $i<$json_data->section->support_title->loop_number; $i++)
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label class="form-label">{{ $json_data->section->support_title->lable .' '. ($i+1) }}</label>
                                            <input type="text" name="array[section][support_title][text][{{ $i }}]" class="form-control" id="{{ ($json_data->section->support_title->lable ?? '').'_'.$i }}" value="{{ $json_data->section->support_title->text->{$i} ?? '' }}">
                                        </div>
                                    </div>
                                </div>
                                @endfor
                            @else
                            <input type="text" name="array[section][support_title][text]" class="form-control" value="{{ $json_data->section->support_title->text ?? '' }}"
                                    placeholder="{{ $json_data->section->support_title->placeholder }}" id="{{ $json_data->section->support_title->slug }}">
                            @endif

                        </div>
                    </div>
                    @endif
                    @if (isset($json_data->section->support_value))
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label class="form-label">{{ $json_data->section->support_value->lable }}</label>
                            <input type="hidden" name="array[section][support_value][slug]" class="form-control" value="{{ $json_data->section->support_value->slug ?? '' }}">
                            <input type="hidden" name="array[section][support_value][lable]" class="form-control" value="{{ $json_data->section->support_value->lable ?? '' }}">
                            <input type="hidden" name="array[section][support_value][type]" class="form-control" value="{{ $json_data->section->support_value->type ?? '' }}">
                            <input type="hidden" name="array[section][support_value][placeholder]" class="form-control" value="{{ $json_data->section->support_value->placeholder ?? '' }}">

                                    @if (isset($json_data->section->support_value->text) && (is_array($json_data->section->support_value->text) || is_object($json_data->section->support_value->text)) && isset($json_data->section->support_value->loop_number))
                                <hr/>
                                @for($i=0; $i<$json_data->section->support_value->loop_number; $i++)
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label class="form-label">{{ $json_data->section->support_value->lable .' '. ($i+1) }}</label>
                                            <input type="text" name="array[section][support_value][text][{{ $i }}]" class="form-control" id="{{ ($json_data->section->support_value->lable ?? '').'_'.$i }}" value="{{ $json_data->section->support_value->text->{$i} ?? '' }}">
                                        </div>
                                    </div>
                                </div>
                                @endfor
                            @else
                            <input type="text" name="array[section][support_value][text]" class="form-control" value="{{ $json_data->section->support_value->text ?? '' }}"
                                    placeholder="{{ $json_data->section->support_value->placeholder }}" id="{{ $json_data->section->support_value->slug }}">
                            @endif

                        </div>
                    </div>
                    @endif
                    @if(isset($json_data->section->sub_title))
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label class="form-label">{{ $json_data->section->sub_title->lable }}</label>
                            <input type="hidden" name="array[section][sub_title][slug]" class="form-control" value="{{ $json_data->section->sub_title->slug ?? '' }}">
                            <input type="hidden" name="array[section][sub_title][lable]" class="form-control" value="{{ $json_data->section->sub_title->lable ?? '' }}">
                            <input type="hidden" name="array[section][sub_title][type]" class="form-control" value="{{ $json_data->section->sub_title->type ?? '' }}">
                            <input type="hidden" name="array[section][sub_title][placeholder]" class="form-control" value="{{ $json_data->section->sub_title->placeholder ?? '' }}">
                            @if (isset($json_data->section->sub_title->text) && (is_array($json_data->section->sub_title->text) || is_object($json_data->section->sub_title->text)) && isset($json_data->section->sub_title->loop_number))
                                <hr/>
                                @for($i=0; $i<$json_data->section->sub_title->loop_number; $i++)
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label class="form-label">{{ $json_data->section->sub_title->lable .' '. ($i+1) }}</label>
                                            <input type="text" name="array[section][sub_title][text][{{ $i }}]" class="form-control" id="{{ ($json_data->section->sub_title->lable ?? '').'_'.$i }}" value="{{ $json_data->section->sub_title->text->{$i} ?? '' }}">
                                        </div>
                                    </div>
                                </div>
                                @endfor
                            @else
                            <input type="text" name="array[section][sub_title][text]" class="form-control" value="{{ $json_data->section->sub_title->text ?? '' }}"
                                    placeholder="{{ $json_data->section->sub_title->placeholder }}" id="{{ $json_data->section->sub_title->slug }}">
                            @endif
                        </div>
                    </div>
                    @endif
                    @if(isset($json_data->section->lable_x))
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label class="form-label">{{ $json_data->section->lable_x->lable }}</label>
                            <input type="hidden" name="array[section][lable_x][slug]" class="form-control" value="{{ $json_data->section->lable_x->slug ?? '' }}">
                            <input type="hidden" name="array[section][lable_x][lable]" class="form-control" value="{{ $json_data->section->lable_x->lable ?? '' }}">
                            <input type="hidden" name="array[section][lable_x][type]" class="form-control" value="{{ $json_data->section->lable_x->type ?? '' }}">
                            <input type="hidden" name="array[section][lable_x][placeholder]" class="form-control" value="{{ $json_data->section->lable_x->placeholder ?? '' }}">
                            @if (isset($json_data->section->lable_x->text) && (is_array($json_data->section->lable_x->text) || is_object($json_data->section->lable_x->text)) && isset($json_data->section->lable_x->loop_number))
                                <hr/>
                                @for($i=0; $i<$json_data->section->lable_x->loop_number; $i++)
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label class="form-label">{{ $json_data->section->lable_x->lable .' '. ($i+1) }}</label>
                                            <input type="text" name="array[section][lable_x][text][{{ $i }}]" class="form-control" id="{{ ($json_data->section->lable_x->lable ?? '').'_'.$i }}" value="{{ $json_data->section->lable_x->text->{$i} ?? '' }}">
                                        </div>
                                    </div>
                                </div>
                                @endfor
                            @else
                            <input type="text" name="array[section][lable_x][text]" class="form-control" value="{{ $json_data->section->lable_x->text ?? '' }}"
                                    placeholder="{{ $json_data->section->lable_x->placeholder }}" id="{{ $json_data->section->lable_x->slug }}">
                            @endif


                        </div>
                    </div>
                    @endif
                    @if(isset($json_data->section->lable_y))
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label class="form-label">{{ $json_data->section->lable_y->lable }}</label>
                            <input type="hidden" name="array[section][lable_y][slug]" class="form-control" value="{{ $json_data->section->lable_y->slug ?? '' }}">
                            <input type="hidden" name="array[section][lable_y][lable]" class="form-control" value="{{ $json_data->section->lable_y->lable ?? '' }}">
                            <input type="hidden" name="array[section][lable_y][type]" class="form-control" value="{{ $json_data->section->lable_y->type ?? '' }}">
                            <input type="hidden" name="array[section][lable_y][placeholder]" class="form-control" value="{{ $json_data->section->lable_y->placeholder ?? '' }}">

                                    @if (isset($json_data->section->lable_y->text) && (is_array($json_data->section->lable_y->text) || is_object($json_data->section->lable_y->text)) && isset($json_data->section->lable_y->loop_number))
                                <hr/>
                                @for($i=0; $i<$json_data->section->lable_y->loop_number; $i++)
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label class="form-label">{{ $json_data->section->lable_y->lable .' '. ($i+1) }}</label>
                                            <input type="text" name="array[section][lable_y][text][{{ $i }}]" class="form-control" id="{{ ($json_data->section->lable_y->lable ?? '').'_'.$i }}" value="{{ $json_data->section->lable_y->text->{$i} ?? '' }}">
                                        </div>
                                    </div>
                                </div>
                                @endfor
                            @else
                            <input type="text" name="array[section][lable_y][text]" class="form-control" value="{{ $json_data->section->lable_y->text ?? '' }}"
                                    placeholder="{{ $json_data->section->lable_y->placeholder }}" id="{{ $json_data->section->lable_y->slug }}">
                            @endif

                        </div>
                    </div>
                    @endif
                    @if(isset($json_data->section->copy_right))
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label class="form-label">{{ $json_data->section->copy_right->lable }}</label>
                            <input type="hidden" name="array[section][copy_right][slug]" class="form-control" value="{{ $json_data->section->copy_right->slug ?? '' }}">
                            <input type="hidden" name="array[section][copy_right][lable]" class="form-control" value="{{ $json_data->section->copy_right->lable ?? '' }}">
                            <input type="hidden" name="array[section][copy_right][type]" class="form-control" value="{{ $json_data->section->copy_right->type ?? '' }}">
                            <input type="hidden" name="array[section][copy_right][placeholder]" class="form-control" value="{{ $json_data->section->copy_right->placeholder ?? '' }}">
                            <input type="text" name="array[section][copy_right][text]" class="form-control" value="{{ $json_data->section->copy_right->text ?? '' }}"
                                    placeholder="{{ $json_data->section->copy_right->placeholder }}" id="{{ $json_data->section->copy_right->slug }}">

                        </div>
                    </div>
                    @endif
                    @if(isset($json_data->section->privacy_policy))
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label class="form-label">{{ $json_data->section->privacy_policy->lable }}</label>
                            <input type="hidden" name="array[section][privacy_policy][slug]" class="form-control" value="{{ $json_data->section->privacy_policy->slug ?? '' }}">
                            <input type="hidden" name="array[section][privacy_policy][lable]" class="form-control" value="{{ $json_data->section->privacy_policy->lable ?? '' }}">
                            <input type="hidden" name="array[section][privacy_policy][type]" class="form-control" value="{{ $json_data->section->privacy_policy->type ?? '' }}">
                            <input type="hidden" name="array[section][privacy_policy][placeholder]" class="form-control" value="{{ $json_data->section->privacy_policy->placeholder ?? '' }}">
                            <div class="row">
                                <div class="col-6">
                                <label class="form-label">{{ __('Label') }}</label>
                                <input type="text" name="array[section][privacy_policy][text]" class="form-control" value="{{ $json_data->section->privacy_policy->text ?? '' }}"
                                    placeholder="{{ $json_data->section->privacy_policy->placeholder }}" id="{{ $json_data->section->privacy_policy->slug }}">
                                </div>
                                <div class="col-6">
                                <label class="form-label">{{ __('Link') }}</label>
                                    <input type="text" name="array[section][privacy_policy][link]" class="form-control" value="{{ $json_data->section->privacy_policy->link ?? '' }}"
                                    placeholder="{{ $json_data->section->privacy_policy->placeholder }}">
                                </div>
                            </div>

                        </div>
                    </div>
                    @endif
                    @if(isset($json_data->section->terms_and_conditions))
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label class="form-label">{{ $json_data->section->terms_and_conditions->lable }}</label>
                            <input type="hidden" name="array[section][terms_and_conditions][slug]" class="form-control" value="{{ $json_data->section->terms_and_conditions->slug ?? '' }}">
                            <input type="hidden" name="array[section][terms_and_conditions][lable]" class="form-control" value="{{ $json_data->section->terms_and_conditions->lable ?? '' }}">
                            <input type="hidden" name="array[section][terms_and_conditions][type]" class="form-control" value="{{ $json_data->section->terms_and_conditions->type ?? '' }}">
                            <input type="hidden" name="array[section][terms_and_conditions][placeholder]" class="form-control" value="{{ $json_data->section->terms_and_conditions->placeholder ?? '' }}">
                            <div class="row">
                                <div class="col-6 ">
                                <label class="form-label">{{ __('Label') }}</label>
                                <input type="text" name="array[section][terms_and_conditions][text]" class="form-control" value="{{ $json_data->section->terms_and_conditions->text ?? '' }}"
                                    placeholder="{{ $json_data->section->terms_and_conditions->placeholder }}" id="{{ $json_data->section->terms_and_conditions->slug }}">
                                </div>
                                <div class="col-6">
                                <label class="form-label">{{ __('Link') }}</label>
                                    <input type="text" name="array[section][terms_and_conditions][link]" class="form-control" value="{{ $json_data->section->terms_and_conditions->link ?? '' }}"
                                    placeholder="{{ $json_data->section->terms_and_conditions->placeholder }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    @if(isset($json_data->section->button))
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label class="form-label">{{ $json_data->section->button->lable }}</label>
                            <input type="hidden" name="array[section][button][slug]" class="form-control" value="{{ $json_data->section->button->slug ?? '' }}">
                            <input type="hidden" name="array[section][button][lable]" class="form-control" value="{{ $json_data->section->button->lable ?? '' }}">
                            <input type="hidden" name="array[section][button][type]" class="form-control" value="{{ $json_data->section->button->type ?? '' }}">
                            <input type="hidden" name="array[section][button][placeholder]" class="form-control" value="{{ $json_data->section->button->placeholder ?? '' }}">
                            @if (isset($json_data->section->button->text) && (is_array($json_data->section->button->text) || is_object($json_data->section->button->text)) && isset($json_data->section->button->loop_number))
                                <hr/>
                                @for($i=0; $i<$json_data->section->button->loop_number; $i++)
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label class="form-label">{{ $json_data->section->button->lable .' '. ($i+1) }}</label>
                                            <input type="text" name="array[section][button][text][{{ $i }}]" class="form-control" id="{{ ($json_data->section->button->lable ?? '').'_'.$i }}" value="{{ $json_data->section->button->text->{$i} ?? '' }}">
                                        </div>
                                    </div>
                                </div>
                                @endfor
                            @else
                            <input type="text" name="array[section][button][text]" class="form-control" value="{{ $json_data->section->button->text ?? '' }}"
                                    placeholder="{{ $json_data->section->button->placeholder }}" id="{{ $json_data->section->button->slug }}">
                            @endif


                        </div>
                    </div>
                    @endif
                    @if(isset($json_data->section->button_first))
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label class="form-label">{{ $json_data->section->button_first->lable }}</label>
                                <input type="hidden" name="array[section][button_first][slug]" class="form-control" value="{{ $json_data->section->button_first->slug ?? '' }}">
                                <input type="hidden" name="array[section][button_first][lable]" class="form-control" value="{{ $json_data->section->button_first->lable ?? '' }}">
                                <input type="hidden" name="array[section][button_first][type]" class="form-control" value="{{ $json_data->section->button_first->type ?? '' }}">
                                <input type="hidden" name="array[section][button_first][placeholder]" class="form-control" value="{{ $json_data->section->button_first->placeholder ?? '' }}">
                                <input type="text" name="array[section][button_first][text]" class="form-control" value="{{ $json_data->section->button_first->text ?? '' }}"
                                        placeholder="{{ $json_data->section->button_first->placeholder }}" id="{{ $json_data->section->button_first->slug ?? '' }}">

                            </div>
                        </div>
                    @endif
                    @if(isset($json_data->section->button_second))
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label class="form-label">{{ $json_data->section->button_second->lable }}</label>
                                <input type="hidden" name="array[section][button_second][slug]" class="form-control" value="{{ $json_data->section->button_second->slug ?? '' }}">
                                <input type="hidden" name="array[section][button_second][lable]" class="form-control" value="{{ $json_data->section->button_second->lable ?? '' }}">
                                <input type="hidden" name="array[section][button_second][type]" class="form-control" value="{{ $json_data->section->button_second->type ?? '' }}">
                                <input type="hidden" name="array[section][button_second][placeholder]" class="form-control" value="{{ $json_data->section->button_second->placeholder ?? '' }}">
                                <input type="text" name="array[section][button_second][text]" class="form-control" value="{{ $json_data->section->button_second->text ?? '' }}"
                                        placeholder="{{ $json_data->section->button_second->placeholder }}" id="{{ $json_data->section->button_second->slug ?? '' }}">

                            </div>
                        </div>
                        @endif
                    @if(isset($json_data->section->description))
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label class="form-label">{{ $json_data->section->description->lable }}</label>
                            <input type="hidden" name="array[section][description][slug]" class="form-control" value="{{ $json_data->section->description->slug ?? '' }}">
                            <input type="hidden" name="array[section][description][lable]" class="form-control" value="{{ $json_data->section->description->lable ?? '' }}">
                            <input type="hidden" name="array[section][description][type]" class="form-control" value="{{ $json_data->section->description->type ?? '' }}">
                            <input type="hidden" name="array[section][description][placeholder]" class="form-control" value="{{ $json_data->section->description->placeholder ?? '' }}">
                            <textarea type="text" name="array[section][description][text]" class="form-control"
                                    placeholder="{{ $json_data->section->description->placeholder }}" id="{{ $json_data->section->description->slug }}"> {{ $json_data->section->description->text }} </textarea>

                        </div>
                    </div>
                    @endif
                    @if(isset($json_data->section->other_description))
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label class="form-label">{{ $json_data->section->other_description->lable }}</label>
                            <input type="hidden" name="array[section][other_description][slug]" class="form-control" value="{{ $json_data->section->other_description->slug ?? '' }}">
                            <input type="hidden" name="array[section][other_description][lable]" class="form-control" value="{{ $json_data->section->other_description->lable ?? '' }}">
                            <input type="hidden" name="array[section][other_description][type]" class="form-control" value="{{ $json_data->section->other_description->type ?? '' }}">
                            <input type="hidden" name="array[section][other_description][placeholder]" class="form-control" value="{{ $json_data->section->other_description->placeholder ?? '' }}">
                            <textarea type="text" name="array[section][other_description][text]" class="form-control"
                                    placeholder="{{ $json_data->section->other_description->placeholder }}" id="{{ $json_data->section->other_description->slug }}"> {{ $json_data->section->other_description->text }} </textarea>

                        </div>
                    </div>
                    @endif
                    @if(isset($json_data->section->newsletter_title))
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label class="form-label">{{ $json_data->section->newsletter_title->lable }}</label>
                            <input type="hidden" name="array[section][newsletter_title][slug]" class="form-control" value="{{ $json_data->section->newsletter_title->slug ?? '' }}">
                            <input type="hidden" name="array[section][newsletter_title][lable]" class="form-control" value="{{ $json_data->section->newsletter_title->lable ?? '' }}">
                            <input type="hidden" name="array[section][newsletter_title][type]" class="form-control" value="{{ $json_data->section->newsletter_title->type ?? '' }}">
                            <input type="hidden" name="array[section][newsletter_title][placeholder]" class="form-control" value="{{ $json_data->section->newsletter_title->placeholder ?? '' }}">
                            <input type="text" name="array[section][newsletter_title][text]" class="form-control"
                                    placeholder="{{ $json_data->section->newsletter_title->placeholder }}" id="{{ $json_data->section->newsletter_title->slug }}" value="{{ $json_data->section->newsletter_title->text ?? '' }}">

                        </div>
                    </div>
                    @endif
                    @if(isset($json_data->section->newsletter_button))
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label class="form-label">{{ $json_data->section->newsletter_button->lable }}</label>
                            <input type="hidden" name="array[section][newsletter_button][slug]" class="form-control" value="{{ $json_data->section->newsletter_button->slug ?? '' }}">
                            <input type="hidden" name="array[section][newsletter_button][lable]" class="form-control" value="{{ $json_data->section->newsletter_button->lable ?? '' }}">
                            <input type="hidden" name="array[section][newsletter_button][type]" class="form-control" value="{{ $json_data->section->newsletter_button->type ?? '' }}">
                            <input type="hidden" name="array[section][newsletter_button][placeholder]" class="form-control" value="{{ $json_data->section->newsletter_button->placeholder ?? '' }}">
                            <input type="text" name="array[section][newsletter_button][text]" class="form-control"
                                    placeholder="{{ $json_data->section->newsletter_button->placeholder }}" id="{{ $json_data->section->newsletter_button->slug }}" value="{{ $json_data->section->newsletter_button->text ?? '' }}">

                        </div>
                    </div>
                    @endif
                    @if(isset($json_data->section->newsletter_sub_title))
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label class="form-label">{{ $json_data->section->newsletter_sub_title->lable }}</label>
                            <input type="hidden" name="array[section][newsletter_sub_title][slug]" class="form-control" value="{{ $json_data->section->newsletter_sub_title->slug ?? '' }}">
                            <input type="hidden" name="array[section][newsletter_sub_title][lable]" class="form-control" value="{{ $json_data->section->newsletter_sub_title->lable ?? '' }}">
                            <input type="hidden" name="array[section][newsletter_sub_title][type]" class="form-control" value="{{ $json_data->section->newsletter_sub_title->type ?? '' }}">
                            <input type="hidden" name="array[section][newsletter_sub_title][placeholder]" class="form-control" value="{{ $json_data->section->newsletter_sub_title->placeholder ?? '' }}">
                            <textarea type="text" name="array[section][newsletter_sub_title][text]" class="form-control"
                                    placeholder="{{ $json_data->section->newsletter_sub_title->placeholder }}" id="{{ $json_data->section->newsletter_sub_title->slug }}"> {{ $json_data->section->newsletter_sub_title->text }} </textarea>

                        </div>
                    </div>
                    @endif
                    @if(isset($json_data->section->newsletter_description))
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label class="form-label">{{ $json_data->section->newsletter_description->lable }}</label>
                            <input type="hidden" name="array[section][newsletter_description][slug]" class="form-control" value="{{ $json_data->section->newsletter_description->slug ?? '' }}">
                            <input type="hidden" name="array[section][newsletter_description][lable]" class="form-control" value="{{ $json_data->section->newsletter_description->lable ?? '' }}">
                            <input type="hidden" name="array[section][newsletter_description][type]" class="form-control" value="{{ $json_data->section->newsletter_description->type ?? '' }}">
                            <input type="hidden" name="array[section][newsletter_description][placeholder]" class="form-control" value="{{ $json_data->section->newsletter_description->placeholder ?? '' }}">
                            <textarea type="text" name="array[section][newsletter_description][text]" class="form-control"
                                    placeholder="{{ $json_data->section->newsletter_description->placeholder }}" id="{{ $json_data->section->newsletter_description->slug }}"> {{ $json_data->section->newsletter_description->text }} </textarea>

                        </div>
                    </div>
                    @endif
                    @if(isset($json_data->section->footer_cookie))
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label class="form-label">{{ $json_data->section->footer_cookie->lable }}</label>
                                <input type="hidden" name="array[section][footer_cookie][slug]" class="form-control" value="{{ $json_data->section->footer_cookie->slug ?? '' }}">
                                <input type="hidden" name="array[section][footer_cookie][lable]" class="form-control" value="{{ $json_data->section->footer_cookie->lable ?? '' }}">
                                <input type="hidden" name="array[section][footer_cookie][type]" class="form-control" value="{{ $json_data->section->footer_cookie->type ?? '' }}">
                                <input type="hidden" name="array[section][footer_cookie][placeholder]" class="form-control" value="{{ $json_data->section->footer_cookie->placeholder ?? '' }}">
                                <input type="text" name="array[section][footer_cookie][text]" class="form-control" value="{{ $json_data->section->footer_cookie->text ?? '' }}"
                                        placeholder="{{ $json_data->section->footer_cookie->placeholder }}" id="{{ $json_data->section->footer_cookie->slug }}">
                            </div>
                        </div>
                    @endif
                    @if(isset($json_data->section->footer_link))
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label class="form-label">{{ $json_data->section->footer_link->lable }}</label>
                            <input type="hidden" name="array[section][footer_link][slug]" class="form-control" value="{{ $json_data->section->footer_link->slug ?? '' }}">
                            <input type="hidden" name="array[section][footer_link][lable]" class="form-control" value="{{ $json_data->section->footer_link->lable ?? '' }}">
                            <input type="hidden" name="array[section][footer_link][type]" class="form-control" value="{{ $json_data->section->footer_link->type ?? '' }}">
                            <input type="hidden" name="array[section][footer_link][text]" class="form-control" value="{{ $json_data->section->footer_link->text ?? '' }}">
                            <input type="hidden" name="array[section][footer_link][loop_number]" class="form-control" value="{{ $json_data->section->footer_link->loop_number ?? '' }}">
                            <hr/>
                            @if (isset($json_data->section->footer_link->type) && ($json_data->section->footer_link->type == 'array') && isset($json_data->section->footer_link->loop_number))
                                @for($i=0; $i<$json_data->section->footer_link->loop_number; $i++)
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label class="form-label">{{ __('Social Link') }}</label>
                                            <input type="text" name="array[section][footer_link][social_link][{{ $i }}]" class="form-control" id="social_link_{{ $i }}" value="{{ $json_data->section->footer_link->social_link->{$i} ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label class="form-label">{{ __('Social Icon') }}</label>
                                            <input type="file" name="array[section][footer_link][social_icon][{{ $i }}][text]" class="form-control" id="social_icon_{{ $i }}">
                                            <input type="hidden" name="array[section][footer_link][social_icon][{{ $i }}][image]" class="form-control" id="social_icon_{{ $i }}" value="{{ $json_data->section->footer_link->social_icon->{$i}->image ?? '' }}">

                                            <img src="{{ asset($json_data->section->footer_link->social_icon->{$i}->image ?? '') }}" class="{{ 'social_icon_'. $i .'_preview' }} social_icon" accept="image/*">
                                        </div>
                                    </div>
                                </div>
                                @endfor
                           @endif
                        </div>
                    </div>
                    @endif
                    @if(isset($json_data->section->announce_title))
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label class="form-label">{{ $json_data->section->announce_title->lable }}</label>
                            <input type="hidden" name="array[section][announce_title][slug]" class="form-control" value="{{ $json_data->section->announce_title->slug ?? '' }}">
                            <input type="hidden" name="array[section][announce_title][lable]" class="form-control" value="{{ $json_data->section->announce_title->lable ?? '' }}">
                            <input type="hidden" name="array[section][announce_title][type]" class="form-control" value="{{ $json_data->section->announce_title->type ?? '' }}">
                            <input type="hidden" name="array[section][announce_title][text]" class="form-control" value="{{ $json_data->section->announce_title->text ?? '' }}">
                            <input type="hidden" name="array[section][announce_title][loop_number]" class="form-control" value="{{ $json_data->section->announce_title->loop_number ?? '' }}">
                            <hr/>
                            @if (isset($json_data->section->announce_title->type) && ($json_data->section->announce_title->type == 'array') && isset($json_data->section->announce_title->loop_number))
                            @for($i=0; $i<$json_data->section->announce_title->loop_number; $i++)
                                <input type="text" name="array[section][announce_title][annouce_text][{{$i}}][text]"
                                class="form-control"
                                value="{{ $json_data->section->announce_title->annouce_text->{$i}->text ?? '' }}"
                                placeholder="{{ $json_data->section->announce_title->placeholder }}"
                                id="{{ $json_data->section->announce_title->slug.'_'.$i }}">
                                @endfor
                           @endif
                        </div>
                    </div>
                    @endif
                    @if(isset($json_data->section->image))
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label class="form-label">{{ $json_data->section->image->lable }}</label>
                            <input type="hidden" name="array[section][image][slug]" class="form-control" value="{{ $json_data->section->image->slug ?? '' }}">
                            <input type="hidden" name="array[section][image][lable]" class="form-control" value="{{ $json_data->section->image->lable ?? '' }}">
                            <input type="hidden" name="array[section][image][type]" class="form-control" value="{{ $json_data->section->image->type ?? '' }}">
                            <input type="hidden" name="array[section][image][placeholder]" class="form-control" value="{{ $json_data->section->image->placeholder ?? '' }}">
                            @if (isset($json_data->section->image->image) && (is_array($json_data->section->image->image) || is_object($json_data->section->image->image)) && isset($json_data->section->image->loop_number))
                                <hr/>
                                @for($i=0; $i<$json_data->section->image->loop_number; $i++)
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label class="form-label">{{ $json_data->section->image->lable .' '. ($i+1) }}</label>
                                            <input type="hidden" name="array[section][image][image][{{$i}}]" class="form-control" value="{{ $json_data->section->image->image->{$i} ?? ''    }}">
                            <input type="file" name="array[section][image][text][{{$i}}]" class="form-control"
                                    placeholder="{{ $json_data->section->image->placeholder }}" id="{{ $json_data->section->image->slug }}" multiple>

                                    <img src="{{ asset($json_data->section->image->image->{$i}) }}" style="width: 100px; height: 100px;" class="{{ $json_data->section->image->slug. $i .'_preview' }}" accept="image/*" multiple>
                                        </div>
                                    </div>
                                </div>
                                @endfor
                            @else
                                @if (is_array($json_data->section->image->image) || is_object($json_data->section->image->image))

                                <input type="file" name="array[section][image][text][]" class="form-control"
                                        placeholder="{{ $json_data->section->image->placeholder }}" id="{{ $json_data->section->image->slug }}" multiple>

                                        @foreach(objectToArray($json_data->section->image->image) as $key => $image)
                                        <input type="hidden" name="array[section][image][image][]" class="form-control" value="{{ $image }}">
                                        <img src="{{ asset($image) }}" style="width:80%; height: 200px;object-fit: cover;margin-top: 10px;" class="{{ $json_data->section->image->slug. $key .'_preview' }}" accept="image/*" multiple>
                                        @endforeach

                                @else
                                <input type="hidden" name="array[section][image][image]" class="form-control" value="{{ $json_data->section->image->image ?? '' }}">
                                <input type="file" name="array[section][image][text]" class="form-control"
                                        placeholder="{{ $json_data->section->image->placeholder }}" id="{{ $json_data->section->image->slug }}"  accept="image/*, video/*">
                                <img src="{{ asset($json_data->section->image->image) }}" style="width:80%; height: 200px;object-fit: cover;margin-top: 10px;" class="{{ $json_data->section->image->slug.'_preview' }}" accept="image/*">
                                @endif
                            @endif
                        </div>
                    </div>
                    @endif


                    @if(isset($json_data->section->image_left))
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label class="form-label">{{ $json_data->section->image_left->lable }}</label>
                            <input type="hidden" name="array[section][image_left][slug]" class="form-control" value="{{ $json_data->section->image_left->slug ?? '' }}">
                            <input type="hidden" name="array[section][image_left][lable]" class="form-control" value="{{ $json_data->section->image_left->lable ?? '' }}">
                            <input type="hidden" name="array[section][image_left][type]" class="form-control" value="{{ $json_data->section->image_left->type ?? '' }}">
                            <input type="hidden" name="array[section][image_left][placeholder]" class="form-control" value="{{ $json_data->section->image_left->placeholder ?? '' }}">
                            @if (is_array($json_data->section->image_left->image) || is_object($json_data->section->image_left->image))
                            <input type="hidden" name="array[section][image_left][image][]" class="form-control" value="{{ json_encode($json_data->section->image_left->image ?? []) }}">
                            <input type="file" name="array[section][image_left][text][]" class="form-control"
                                    placeholder="{{ $json_data->section->image_left->placeholder }}" id="{{ $json_data->section->image_left->slug }}" multiple>

                                    @foreach(objectToArray($json_data->section->image_left->image) as $key => $image)
                                    <img src="{{ asset($image) }}" style="width:80%; height: 200px;object-fit: cover;margin-top: 10px;" class="{{ $json_data->section->image_left->slug. $key .'_preview' }}" accept="image/*" multiple>
                                    @endforeach

                            @else
                            <input type="hidden" name="array[section][image_left][image]" class="form-control" value="{{ $json_data->section->image_left->image ?? '' }}">
                            <input type="file" name="array[section][image_left][text]" class="form-control"
                                    placeholder="{{ $json_data->section->image_left->placeholder }}" id="{{ $json_data->section->image_left->slug }}">
                            <img src="{{ asset($json_data->section->image_left->image) }}" style="width:80%; height: 200px;object-fit: cover;margin-top: 10px;" class="{{ $json_data->section->image_left->slug.'_preview' }}" accept="image/*">
                            @endif

                        </div>
                    </div>
                    @endif
                    @if(isset($json_data->section->image_right))
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label class="form-label">{{ $json_data->section->image_right->lable }}</label>
                            <input type="hidden" name="array[section][image_right][slug]" class="form-control" value="{{ $json_data->section->image_right->slug ?? '' }}">
                            <input type="hidden" name="array[section][image_right][lable]" class="form-control" value="{{ $json_data->section->image_right->lable ?? '' }}">
                            <input type="hidden" name="array[section][image_right][type]" class="form-control" value="{{ $json_data->section->image_right->type ?? '' }}">
                            <input type="hidden" name="array[section][image_right][placeholder]" class="form-control" value="{{ $json_data->section->image_right->placeholder ?? '' }}">
                            @if (is_array($json_data->section->image_right->image) || is_object($json_data->section->image_right->image))
                            <input type="hidden" name="array[section][image_right][image][]" class="form-control" value="{{ json_encode($json_data->section->image_right->image ?? []) }}">
                            <input type="file" name="array[section][image_right][text][]" class="form-control"
                                    placeholder="{{ $json_data->section->image_right->placeholder }}" id="{{ $json_data->section->image_right->slug }}" multiple>

                                    @foreach(objectToArray($json_data->section->image_right->image) as $key => $image)
                                    <img src="{{ asset($image) }}" style="width:80%; height: 200px;object-fit: cover;margin-top: 10px;" class="{{ $json_data->section->image_right->slug. $key .'_preview' }}" accept="image/*" multiple>
                                    @endforeach

                            @else
                            <input type="hidden" name="array[section][image_right][image]" class="form-control" value="{{ $json_data->section->image_right->image ?? '' }}">
                            <input type="file" name="array[section][image_right][text]" class="form-control"
                                    placeholder="{{ $json_data->section->image_right->placeholder }}" id="{{ $json_data->section->image_right->slug }}">
                            <img src="{{ asset($json_data->section->image_right->image) }}" style="width:80%; height: 200px;object-fit: cover;margin-top: 10px;" class="{{ $json_data->section->image_right->slug.'_preview' }}" accept="image/*">
                            @endif

                        </div>
                    </div>
                    @endif
                    @if (isset($json_data->section->product_type))
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label
                                class="form-label">{{ $json_data->section->product_type->lable }}</label>
                            <input type="hidden" name="array[section][product_type][slug]" class="form-control" value="{{ $json_data->section->product_type->slug ?? '' }}">
                            <input type="hidden" name="array[section][product_type][lable]" class="form-control" value="{{ $json_data->section->product_type->lable ?? '' }}">
                            <input type="hidden" name="array[section][product_type][type]" class="form-control" value="{{ $json_data->section->product_type->type ?? '' }}">
                            <input type="hidden" name="array[section][product_type][placeholder]" class="form-control" value="{{ $json_data->section->product_type->placeholder ?? '' }}">
                            <select class="form-control"
                                    name="array[section][product_type][text]"
                                    rows="3" id="{{ $json_data->section->product_type->slug }}">
                                <option value>{{ __('Select Option') }}</option>
                                    @foreach(config('theme_form_options.product') as $key => $option)
                                    <option value="{{ $key }}" {{ ($key == $json_data->section->product_type->text) ? 'selected' : '' }}>{{ $option }}</option>
                                    @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label class="form-label product_ids">{{ __('Custom Products') }}</label>
                            @php
                                $productIds = isset($json_data->section->product_type->product_ids) && is_object($json_data->section->product_type->product_ids) ? (array) $json_data->section->product_type->product_ids: [];
                                if (!isset($json_data->section->product_type->product_ids)) {
                                    $productIds = $json_data->section->product_ids ?? [];
                                }
                            @endphp
                                <select class="form-control product_ids"
                                        name="array[section][product_type][product_ids][]"
                                        id="product_ids" multiple>
                                        <option value>Select Option</option>
                                        @foreach($produtcs as $product)
                                        <option value="{{ $product->id }}" {{ in_array($product->id, $productIds) ? 'selected' : '' }}>{{ $product->name }}</option>
                                        @endforeach
                                </select>
                        </div>
                    </div>
                    @endif
                    @if (isset($json_data->section->menu_type))
                        @php
                            $menuIds = isset($json_data->section->menu_type->menu_ids) ? (array) $json_data->section->menu_type->menu_ids : [];
                            if (empty($menuIds)) {
                                $menuIds = isset($json_data->section->menu_ids) ? (array) $json_data->section->menu_ids : [];
                            }
                        @endphp
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label class="form-label">{{ __('Select Menu ') }}</label>
                                <select class="form-control menu_ids"
                                        name="array[section][menu_type][menu_ids][]"
                                        id="menu_ids">
                                    <option value>{{__('Select Option')}}</option>
                                    @foreach($menus as $menu)
                                        <option value="{{ $menu->id }}" {{ in_array($menu->id, $menuIds) ? 'selected' : '' }}>{{ $menu->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endif

                    @if(isset($json_data->section->footer_menu_type))
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label class="form-label">{{ $json_data->section->footer_menu_type->lable }}</label>
                                <input type="hidden" name="array[section][footer_menu_type][slug]" class="form-control" value="{{ $json_data->section->footer_menu_type->slug ?? '' }}">
                                <input type="hidden" name="array[section][footer_menu_type][lable]" class="form-control" value="{{ $json_data->section->footer_menu_type->lable ?? '' }}">
                                <input type="hidden" name="array[section][footer_menu_type][type]" class="form-control" value="{{ $json_data->section->footer_menu_type->type ?? '' }}">
                                <input type="hidden" name="array[section][footer_menu_type][text]" class="form-control" value="{{ $json_data->section->footer_menu_type->text ?? '' }}">
                                <input type="hidden" name="array[section][footer_menu_type][loop_number]" class="form-control" value="{{ $json_data->section->footer_menu_type->loop_number ?? '' }}">
                                <hr/>
                                @if (isset($json_data->section->footer_menu_type->type) && ($json_data->section->footer_menu_type->type == 'array') && isset($json_data->section->footer_menu_type->loop_number))
                                    @for($i=0; $i<$json_data->section->footer_menu_type->loop_number; $i++)
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label class="form-label">{{ __('Footer Title') }}</label>
                                                <input type="text" name="array[section][footer_menu_type][footer_title][{{ $i }}]" class="form-control" id="footer_title_{{ $i }}" value="{{ $json_data->section->footer_menu_type->footer_title->{$i} ?? '' }}" placeholder="Enter text here....">
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label class="form-label">{{ __('Footer Menu') }}</label>
                                                @php
                                                    $menuIds = isset($json_data->section->footer_menu_type->footer_menu_ids) && is_object($json_data->section->footer_menu_type->footer_menu_ids)
                                                        ? (array) $json_data->section->footer_menu_type->footer_menu_ids : [];

                                                    if (!isset($json_data->section->footer_menu_type->footer_menu_ids)) {
                                                        $menuIds = $json_data->section->footer_menu_type->footer_menu_ids ?? [];
                                                    }
                                                @endphp
                                                <select class="form-control" name="array[section][footer_menu_type][footer_menu_ids][]" id="">
                                                    <option value>{{__('Select Option')}}</option>

                                                    @foreach($menus as $menu)
                                                        <option value="{{ $menu->id }}" {{ in_array($menu->id, $menuIds) ? 'selected' : '' }}>{{ $menu->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    @endfor
                            @endif
                            </div>
                        </div>
                    @endif                                                       
                @endif
            </div>
            @endfor
        </div>
        <div class="card-footer">
            @if (isset($json_data->section_slug) && $json_data->section_slug == 'slider' && $json_data->array_type == 'multi-inner-list')
            <div class="row">
                <div class="col-sm-12 text-end">
                    <button class="btn btn-primary btn-badge add-new-slider-btn">{{__('Add New Slider')}}</button>
                    <button class="btn btn-danger btn-badge delete-slider-btn" @if(isset($json_data->loop_number) && $json_data->loop_number < 4) disabled="true" @endif>{{__('Delete Slider')}}</button>
                </div>
            </div>
            @endif
        </div>
    </div>
    {!! Form::close() !!}
@endif
