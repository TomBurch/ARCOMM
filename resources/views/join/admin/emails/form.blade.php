<input
    type="text"
    placeholder="Subject"
    name="subject"
    value="{{ (isset($email)) ? $email->subject : '' }}"
    class="form-control m-b-5"
    maxlength="255">

<textarea
    name="content"
    placeholder="Content"
    class="form-control m-b-5"
    rows="20">{!! (isset($email)) ? $email->content : '' !!}
</textarea>
