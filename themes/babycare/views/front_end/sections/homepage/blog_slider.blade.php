<div class="blog-slider">
    @foreach ($landing_blogs as $blog)
    <div class="blog-itm">
        <div class="blog-inner">
            <div class="blog-img">
                <a href="{{ route('page.article', ['storeSlug'=> $slug,$blog->id]) }}">
                    <img src="{{ get_file($blog->cover_image_path, $currentTheme) }}" alt="blog-img">
                </a>
            </div>
            <div class="blog-content">
                <h4><a href="#">{{$blog->title}}</a> </h4>
                <p>{{$blog->short_description}}</p>
                <a href="{{ route('page.article', ['storeSlug'=> $slug,$blog->id]) }}" class="btn-secondary">
                    {{  $page_json->blog_page->section->section_sub_button->text ?? __('Read more') }}
                </a>
            </div>
        </div>
    </div>
    @endforeach
</div>