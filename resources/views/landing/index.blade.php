@extends('landing.layout.app')
@section('page_name', 'Welcome to EMD')

@push('style')
@endpush

@section('content')

    <section class="hero">
        <div class="hero-display text-center">
            <h1 class="display-3 fw-bold text-white">Simplify Tender Management with <br> Smart Billing & Client Tools</h1>
            <p class="fs-25 my-4 text-white">A lightweight, secure billing platform built for businesses that need <br> speed and mobility. Create clients, add bills, and download <br>
                professional invoices in seconds.</p>
            <button class="btn btn-secondary fs-18 px-5 py-1">Get started</button>
        </div>
    </section>

    <section>
        <div class="container">
            <div class="row align-items-center justify-content-between gx-sm-2 gy-sm-3">
                <div class="col-lg-7 col-sm-12 mb-lg-0 mb-4">
                    <img src="{{ asset('assets/images/landing/image 96.png') }}" class="d-block mx-lg-auto img-fluid" alt="Bootstrap Themes" width="700" height="500" loading="lazy">
                </div>
                <div class="col-lg-5 col-sm-12">
                    <h2 class="display-5 fw-semibold">Custom Software & <br> Digital Marketing, <br> Perfectly Aligned</h2>
                    <p class="fs-16 text-theme fw-normal lh-2 py-3 text-justify">At Living Brains, we believe in the power of innovation and creativity to transform businesses.
                        We specialize in delivering cutting-edge digital marketing strategies combined with custom software solutions that are tailored to meet your unique needs.</p>
                    <a href="#" class="btn btn-theme theme-bg btn-outline-dark fw-semibold radius-15 py-2">Read More<i class="fa fa-chevron-right ms-2"></i></a>
                </div>
            </div>
        </div>
    </section>

    <section>
        <div class="container">
            
        </div>
    </section>

@endsection

@push('script')
@endpush
