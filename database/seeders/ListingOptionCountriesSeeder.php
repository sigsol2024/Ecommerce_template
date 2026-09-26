<?php

namespace Database\Seeders;

use App\Models\ListingOption;
use App\Models\ListingOptionCategory;
use App\Support\CountryFlagEmoji;
use Illuminate\Database\Seeder;

/**
 * Seeds 100+ country root options with regional-indicator flag emoji (ISO alpha-2).
 * Idempotent: matches on category_id + parent_id null + value (country name).
 */
class ListingOptionCountriesSeeder extends Seeder
{
    public function run(): void
    {
        $categoryId = ListingOptionCategory::query()->where('slug', 'country')->value('id');
        if (! $categoryId) {
            return;
        }

        $lines = array_filter(array_map('trim', explode("\n", self::countryCsv())));
        $order = 1;
        foreach ($lines as $line) {
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            $parts = str_getcsv($line);
            if (count($parts) < 2) {
                continue;
            }
            [$code, $name] = [strtoupper(trim($parts[0])), trim($parts[1])];
            if ($code === '' || $name === '') {
                continue;
            }
            $flag = CountryFlagEmoji::fromAlpha2($code);
            ListingOption::query()->updateOrCreate(
                [
                    'category_id' => (int) $categoryId,
                    'parent_id' => null,
                    'value' => $name,
                ],
                [
                    'sort_order' => $order++,
                    'is_active' => true,
                    'flag_emoji' => $flag !== '' ? $flag : null,
                ]
            );
        }
    }

    /**
     * CSV lines: CODE,"Country name" (110+ entries).
     */
    private static function countryCsv(): string
    {
        return <<<'CSV'
AF,Afghanistan
AL,Albania
DZ,Algeria
AD,Andorra
AO,Angola
AR,Argentina
AM,Armenia
AU,Australia
AT,Austria
AZ,Azerbaijan
BS,Bahamas
BH,Bahrain
BD,Bangladesh
BB,Barbados
BY,Belarus
BE,Belgium
BZ,Belize
BJ,Benin
BT,Bhutan
BO,Bolivia
BA,Bosnia and Herzegovina
BW,Botswana
BR,Brazil
BN,Brunei
BG,Bulgaria
BF,Burkina Faso
BI,Burundi
KH,Cambodia
CM,Cameroon
CA,Canada
CV,Cape Verde
TD,Chad
CL,Chile
CN,China
CO,Colombia
CR,Costa Rica
HR,Croatia
CU,Cuba
CY,Cyprus
CZ,Czech Republic
DK,Denmark
DJ,Djibouti
DO,Dominican Republic
EC,Ecuador
EG,Egypt
SV,El Salvador
EE,Estonia
SZ,Eswatini
ET,Ethiopia
FJ,Fiji
FI,Finland
FR,France
GA,Gabon
GM,Gambia
GE,Georgia
DE,Germany
GH,Ghana
GR,Greece
GT,Guatemala
GN,Guinea
GY,Guyana
HT,Haiti
HN,Honduras
HK,Hong Kong
HU,Hungary
IS,Iceland
IN,India
ID,Indonesia
IR,Iran
IQ,Iraq
IE,Ireland
IL,Israel
IT,Italy
JM,Jamaica
JP,Japan
JO,Jordan
KZ,Kazakhstan
KE,Kenya
KW,Kuwait
KG,Kyrgyzstan
LA,Laos
LV,Latvia
LB,Lebanon
LS,Lesotho
LR,Liberia
LY,Libya
LI,Liechtenstein
LT,Lithuania
LU,Luxembourg
MG,Madagascar
MW,Malawi
MY,Malaysia
MV,Maldives
ML,Mali
MT,Malta
MR,Mauritania
MU,Mauritius
MX,Mexico
MD,Moldova
MC,Monaco
MN,Mongolia
ME,Montenegro
MA,Morocco
MZ,Mozambique
MM,Myanmar
NA,Namibia
NP,Nepal
NL,Netherlands
NZ,New Zealand
NI,Nicaragua
NE,Niger
NG,Nigeria
KP,North Korea
MK,North Macedonia
NO,Norway
OM,Oman
PK,Pakistan
PA,Panama
PY,Paraguay
PE,Peru
PH,Philippines
PL,Poland
PT,Portugal
QA,Qatar
RO,Romania
RU,Russia
RW,Rwanda
SA,Saudi Arabia
SN,Senegal
RS,Serbia
SL,Sierra Leone
SG,Singapore
SK,Slovakia
SI,Slovenia
SO,Somalia
ZA,South Africa
KR,South Korea
ES,Spain
LK,Sri Lanka
SD,Sudan
SE,Sweden
CH,Switzerland
SY,Syria
TW,Taiwan
TJ,Tajikistan
TZ,Tanzania
TH,Thailand
TG,Togo
TT,Trinidad and Tobago
TN,Tunisia
TR,Turkey
TM,Turkmenistan
UG,Uganda
UA,Ukraine
AE,United Arab Emirates
GB,United Kingdom
US,United States
UY,Uruguay
UZ,Uzbekistan
VE,Venezuela
VN,Vietnam
YE,Yemen
ZM,Zambia
ZW,Zimbabwe
PR,Puerto Rico
PS,Palestine
GL,Greenland
NC,New Caledonia
PF,French Polynesia
CSV;
    }
}
