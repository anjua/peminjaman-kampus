<table class="kop">
    <tr>
        @if ($kop['logo'] && file_exists($kop['logo']))
        <td style="width: 70px"><img src="{{ $kop['logo'] }}" height="60" alt=""></td>
        @endif
        <td>
            <div class="instansi">{{ $kop['instansi'] }}</div>
            <div>{{ $kop['unit'] }}</div>
        </td>
    </tr>
</table>